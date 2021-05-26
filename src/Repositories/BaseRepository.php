<?php

namespace Easy\Repositories;

use Easy\Interfaces\ILogable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseRepository
{
    protected $filters = [];
    protected $select_fields = ['*'];
    protected $sortable_fields = [];
    protected $orderBy = null;
    protected $orderByAsc = true;
    protected $relationships = null;
    protected $checkDelete = [];
    protected $allow_load_deleted = false;
    protected $split_operator = ':';
    protected $split_relation = '->';
    protected $use_uuid = false;

    /**
     * @return Model
     */
    abstract function getModel();

    /**
     * @param array $data
     * @param Builder|null $query
     * @return Builder
     */
    public
    function search(array $data = array(), ?Builder $query = null)
    {
        if (is_null($query)) {
            $query = $this->getModel()->select($this->select_fields);
        }
        $this->loadRelations($query, $data);
        $this->loadDeletes($query, $data);
        $this->applyOrderBy($query, $data);
        $this->applyFilters($query, $data);
        return $query;
    }


    function create(array $data, $log = true)
    {
        $this->creating($data);
        $model = $this->getModel()->fill($data);
        if ($this->use_uuid){
            $key = $model->getKeyName();
            $model->$key = Str::uuid();
        }
        $model->save();
        $this->created($model, $data);
        if (is_subclass_of($model, ILogable::class) && $log) {
            LogRepository::createAction($model);
        }
        return $model;
    }


    /**
     * @param Model $model
     * @param array $data
     * @param bool $log
     * @return ILogable|Model
     * @throws \Exception
     */
    function update(Model $model, array $data, $log = true)
    {
        return $this->baseUpdate($model, $data, $log, true);
    }

    private function baseUpdate(Model $model, array $data, $log = true, bool $helpers = true)
    {
        $log = is_subclass_of($model, ILogable::class) && $log;
        $old_values = $log ? $model->getLogData(true, true) : null;
        if ($helpers) {
            $this->updating($model, $data);
        }
        $model->update($data);
        if ($helpers) {
            $this->updated($model, $data);
        }
        if ($log) {
            /** @var ILogable $old_model */
            LogRepository::updateAction($old_values, $model->refresh());
        }
        return $model;
    }

    public function updateWithoutHelpers(Model $model, array $data, $log = true)
    {
        return $this->baseUpdate($model, $data, $log, false);
    }


    /**
     * @param Model|int $model
     * @param bool $log
     * @return mixed
     * @throws \Exception
     */
    function delete($model, $log = true)
    {
        if (is_numeric($model)) {
            $model = $this->findOrFail($model);
        }
        /** @var Model $model */
        foreach ($this->checkDelete as $relation) {
            if ($model->load($relation)->$relation()->exists()) {//TODO: probar sin el load()
                throw new \Exception(trans('easy::exceptions.can_not'));
            }
        }
        $this->deleting($model);
        if (is_subclass_of($model, ILogable::class) && $log) {
            LogRepository::deleteAction($model);
        }
        $model->delete();
        $this->deleted($model);
        return $model;
    }

    /**
     * @param int $id
     * @param bool $clean
     * @return Model
     */
    function findOrFail($id, bool $clean = true)
    {
        if ($clean) {
            return $this->getModel()->findOrFail($id);
        } else {
            return $this->search()->where('id', $id)->firstOrFail();
        }

    }


    /**
     * Mapea una colleccion de para una relacion mucho a mucho. Generalmente solo es necesario en caso de relaciones con pivot
     *
     * @param Collection $collection
     * @param string $map_key
     * @param array $to_map
     * @return Collection
     */
    public function mapCollectionSync(Collection $collection, string $map_key = 'id', array $to_map = []): Collection
    {
        return $collection->mapWithKeys(function ($item) use ($to_map, $map_key) {
            return [$item[$map_key] => collect($item)->except($map_key)->only($to_map)];
        });
    }

    #region Private functions
    private function loadRelations(&$query, array $data)
    {
        if (!is_null($this->relationships)) {
            $query = $query->with($this->relationships);
        }
    }

    private function loadDeletes(&$query, array $data)
    {
        if ($this->allow_load_deleted) {
            if (array_key_exists('only_deleted', $data)) {
                $query = $query->onlyTrashed();
            } elseif (array_key_exists('with_deleted', $data)) {
                $query = $query->withTrashed();
            }
        }
    }

    private function applyOrderBy(&$query, array $data)
    {
        $orderBy = $data['sort_by'] ?? null;
        if (!is_null($orderBy)) {
            if (!in_array($orderBy, $this->sortable_fields)) {
                return;
            }
        } elseif (is_null($this->orderBy)) {
            return;
        } else {
            $orderBy = $this->orderBy;
        }
        $orderByAsc = $data['sort_asc'] ?? $this->orderByAsc;
        $query->orderBy($orderBy, $orderByAsc ? 'asc' : 'desc');

    }

    private function applyFilters(&$query, array $data)
    {
        $filter = Arr::only($data, $this->filters);
        $filter = array_filter($filter, 'strlen');
        foreach ($filter as $param => $value) {
            if (isset($filter[$param])) {
                $split = explode(':', $param);
                $filterMethod = 'searchBy' . Str::studly($split[0]);
                if (method_exists(get_called_class(), $filterMethod)) {
                    $this->$filterMethod($query, $value);
                } else {
                    $this->addSearchParam($query, $param, $value);
                }
            }
        }
    }

    private function relationCondition($query, string $relation, string $field, string $operator, $value)
    {
        $query->whereHas($relation, function ($query) use ($relation, $value, $operator, $field) {
            $split_relation = explode($this->split_relation, $field, 2);
            if (count($split_relation) > 1) {
                $this->relationCondition($query, $split_relation[0], $split_relation[1], $operator, $value);
            } else {
                $this->addWhere($query, $field, $operator, $value);
            }
        });
    }

    /**
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param $value
     * @return Builder
     */
    private function addWhere($query, string $field, string $operator, $value)
    {
        return $operator == "null"
            ? ($value == true ? $query->whereNull($field) : $query->whereNotNull($field))
            : $query->where($field, $operator, $value);
    }

    /**
     * @param Builder $query
     * @param string $param
     * @param $value
     */
    private function addSearchParam($query, string $param, $value)
    {
        $split = explode($this->split_operator, $param, 2);
        $filterMethod = 'searchBy' . Str::studly($split[0]);
        if (method_exists(get_called_class(), $filterMethod)) {
            $this->$filterMethod($query, $value);
        } else {
            $operator = "=";
            if (count($split) > 1) {
                $operator = $split[1];
            }
            $split_field = explode($this->split_relation, $split[0], 2);
            if (count($split_field) > 1) {
                $this->relationCondition($query, $split_field[0], $split_field[1], $operator, $value);
            } else {
                $this->addWhere($query, $split[0], $operator, $value);
            }
        }
    }
#endregion

    #region Login
    public function getLogs($model, array $data)
    {
        if (!is_subclass_of($model, ILogable::class)) {
            throw new NotFoundHttpException();
        }
        /** @var ILogable $model */
        $logRepository = new LogRepository();
        $query = $model->logs()->getQuery();
        foreach ($model->getLogableRelations() as $relation) {
            $external_logs = $model->$relation;
            if (is_countable($external_logs)) {
                foreach ($external_logs as $external_log) {
                    if (is_subclass_of($external_log, ILogable::class)) {
                        $query->union($external_log->logs()->newQuery());
                    }
                }
            } else {
                if (is_subclass_of($external_logs, ILogable::class)) {
                    $query->union($external_logs->logs()->newQuery());
                }
            }
        }
        return $logRepository->search($data, $query);
    }
    #endregion

    #region auxiliaries methods
    /**
     * Execute before create
     * @param array $data
     * @throws \Exception
     */
    protected function creating(array &$data): void
    {

    }

    /**
     * Execute after create
     * @param Model $model
     * @param array $data
     * @throws \Exception
     */
    protected function created(Model &$model, array &$data): void
    {
    }

    /**
     * Execute before update
     * @param Model $model
     * @param array $data
     * @throws \Exception
     */
    protected function updating(Model &$model, array &$data): void
    {
    }

    /**
     * Execute after update
     * @param Model $model
     * @param array $data
     * @throws \Exception
     */
    protected function updated(Model &$model, array &$data): void
    {
    }

    /**
     * Execute before delete
     * @param Model $model
     * @throws \Exception
     */
    protected function deleting(Model &$model): void
    {
    }

    /**
     * Execute after delete
     * @param Model $model
     * @throws \Exception
     */
    protected function deleted(Model &$model): void
    {
    }

    #endregion
}

