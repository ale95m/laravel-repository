<?php


namespace Easy\Exports;


use Carbon\Carbon;
use Easy\Helpers\Translate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseExport
{
    protected $repository;
    protected $exportable_fields = [];
    protected $fields_separator = ', ';
    protected $split_relation = ':';
    protected $relation_identifier = '->';

    protected $header = 'Export';
    protected $search_function = 'search';

    public function __construct($repository)
    {
        $this->repository = $repository;
        $this->exportable_fields = collect($this->exportable_fields);
    }

    public function getExcel(array $data, ?string $password)
    {
        $columns = $this->exportable_fields->mapWithKeys(function ($field, $header) {
            $column = [];
            $column['relation'] = null;
            if (!is_numeric($header)) {
                $check_relation = explode($this->relation_identifier, $field, 2);
                if (count($check_relation) > 1) {
                    $split_relation = explode($this->split_relation, $check_relation[1], 2);
                    $column['relation'] = $split_relation[0] == '' ? $header : $split_relation[0];
                    $column['attribute'] = count($split_relation) > 1 ? $split_relation[1] : 'name';
                } else {
                    $column['relation'] = null;
                    $column['attribute'] = $field;
                }
            } else {
                $header = $field;
                $column['relation'] = null;
                $column['attribute'] = $field;
            }
            $column['mapper'] = 'map' . Str::studly($header);
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

    protected function getHeader()
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

    protected function fileName()
    {
        return strtolower(class_basename($this->repository->getModel()));
    }


    private function mapExportableFields(Collection $row, array $fields)
    {
        foreach ($fields as $field) {
            $map_function = 'map' . Str::studly($field);
            if (method_exists(get_called_class(), $map_function)) {
                $row[$field] = $this->$map_function($row[$field]);
            }
        }
    }

    private function fillRow(Model $model, Collection $columns)
    {
        $row = [];
        foreach ($columns as $column) {
            $mapper = $column['mapper'];
            if (method_exists(get_called_class(), $column['mapper'])) {
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
