<?php


namespace Easy\Traits;


use Easy\Interfaces\ILogable;
use Easy\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property array logableRelations
 * @property array logableAttributes
 */
trait HasLogs
{

    public function getLogModel(): string
    {
        $class_name = strtolower(class_basename(get_called_class()));
        return /*ucfirst(trans("models.*/ $class_name/*.name"))*/ ;
    }

    function getLogableAttributes(): array
    {
        return $this->logableAttributes ?? [];
    }

    function getLogableRelations(): array
    {
        return $this->logableRelations ?? [];
    }

    public function getLogData(bool $with_relations, bool $include_null, ?array $only_attributes = null): Collection
    {
        $data = new Collection();
        $attributes = is_null($only_attributes)
            ? $this->getLogableAttributes()
            : array_intersect($this->getLogableAttributes(), $only_attributes);

        array_push($attributes, $this->getKeyName());
        foreach ($attributes as $attribute) {
            if (!$include_null && is_null($this->$attribute)) {
                continue;
            }
            $data[$attribute] = $this->$attribute;
        }
        if ($with_relations) {
            foreach ($this->getLogableRelations() as $relation) {
                $split = explode(':', str_replace(' ', '', $relation));
                $relation = $split[0];
                if (!is_null($only_attributes)) {
                    if (!in_array($relation, $only_attributes)) {
                        continue;
                    }
                }
                $relation_attributes = count($split) > 1? explode(',', $split[1]): null;
                $external_logs = $this->$relation;
                $relation_data = new Collection();
                if (is_countable($external_logs)) {
                    foreach ($external_logs as $external_log) {
                        if (is_subclass_of($external_log, ILogable::class)) {
                            $relation_data->push($external_log->getLogData(true, $include_null, $relation_attributes));
                        }
                    }
                } elseif (is_subclass_of($external_logs, ILogable::class)) {
                    $relation_data->push($external_logs->getLogData(true, $include_null, $relation_attributes));
                }
                $data[$relation] = $relation_data;
            }
        }
        return $data;
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }
}
