<?php


namespace Easy\Exports;


use Carbon\Carbon;
use Easy\Helpers\Translate;
use Easy\Repositories\EasyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property EasyRepository repository
 */
class EasyExcelExport
{
    protected $repository;
    protected array $exportable_fields = [];
    protected string $fields_separator = ', ';
    protected string $split_relation = ':';
    protected string $relation_identifier = '->';
    protected string $default_relation_field = 'name';

    protected string $header = 'Export';
    protected string $search_function = 'search';

    /**
     * @param EasyRepository $repository
     */
    public function __construct(EasyRepository $repository)
    {
        $this->repository = $repository;
        $this->exportable_fields = collect($this->exportable_fields);
    }

    public function getExcel(array $data, ?string $password)
    {
        $columns = $this->exportable_fields->mapWithKeys(function ($field, $header) {
            $column = [];
            $column['relation'] = null;
            if (is_numeric($header)) {
                $header = $field;
                $column['relation'] = null;
                $column['attribute'] = $field;
            } else {
                $check_relation = explode($this->relation_identifier, $field, 2);
                if (count($check_relation) > 1) {
                    $split_relation = explode($this->split_relation, $check_relation[1], 2);
                    $column['relation'] = $split_relation[0] == '' ? $header : $split_relation[0];
                    $column['attribute'] = count($split_relation) > 1 ? $split_relation[1] : $this->default_relation_field;
                } else {
                    $column['relation'] = null;
                    $column['attribute'] = $field;
                }
            }
            $column['mapper_function'] = 'map' . Str::studly($header);
            return [$header => $column];
        });

        $function = $this->search_function;
        $export = $this->repository->$function($data)
            ->chunkMap(function ($item) use ($columns) {
                return $this->fillRow($item, $columns);
            });
        $export->splice(0, 0, [
            [
                $this->getHeader(),
            ],
            $this->getColumns()
        ]);
        return (new StatisticExport($export, $password))->download($this->fileName() . '.xlsx');
    }

    protected function getHeader(): string
    {
        return $this->header;
    }

    private function getColumns()
    {
        return $this->exportable_fields->map(function ($item, $key) {
            $column_name = is_numeric($key) ? $item : $key;
            return ucfirst(Translate::translateAttribute($column_name));
        });
    }

    protected function fileName(): string
    {
        return strtolower(class_basename($this->repository->getModel()));
    }

    private function fillRow(Model $model, Collection $columns)
    {
        $row = [];
        foreach ($columns as $column) {
            $mapper = $column['mapper_function'];
            if (method_exists(get_called_class(), $mapper)) {
                array_push($row, $this->$mapper($model));
                continue;
            }
            $attribute = $column['attribute'];
            $relation_name = $column['relation'];
            $to_pull = null;
            if (is_null($relation_name)) {
                $to_pull = $model->$attribute;
            } else {
                $relation = $model->$relation_name;
                if (!is_null($relation)) {
                    $to_pull =
                        is_subclass_of($relation, Model::class)
                            ? $relation[$attribute]
                            : collect($relation)->implode($attribute, $this->fields_separator);
                }
            }
            array_push($row, $to_pull);
        }
        return $row;
    }
}
