<?php


namespace ale95m\Easy\Repositories;

use ale95m\Easy\Interfaces\ILogable;
use ale95m\Easy\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LogRepository extends BaseRepository
{
    protected $relationships = [
        'user'
    ];

    protected $filters = [
        'action:like',
        'model:like',
        'ip:like',
        'user_id',
        'logable_id',
        'created_at:>',
        'created_at:<',
    ];

    /**
     * @inheritDoc
     */
    function getModel()
    {
        return new Log();
    }

    public static function createLog(string $action, ?string $model = null, $attributes = null, $changes = null, ?string $logable_type = null, ?int $logable_id = null)
    {
        $user_id = null;
        if (Auth::check()) {
            $user_id = Auth::id();
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        Log::create
        ([
            'user_id' => $user_id,
            'ip' => $ip,
            'action' => $action,
            'model' => $model,
            'attributes' => json_encode($attributes),
            'changes' => json_encode($changes),
            'logable_type' => $logable_type,
            'logable_id' => $logable_id
        ]);
    }

    public static function logOutAction()
    {
        self::createLog('logout');
    }

    public static function logInAction()
    {
        self::createLog('login');
    }

    /**
     * @param ILogable|Model $model
     */
    public static function createAction($model)
    {
        self::createLog('create', $model->getLogModel(),
            $model->getLogData(true,false), null,
            get_class($model),
            $model->getKey()
        );
    }

    /**
     * @param Collection $old_data
     * @param ILogable|Model $model
     * @param bool $with_relations
     */
    public static function updateAction($old_data, $model, bool $with_relations = true)
    {
        $collection2 = $model->getLogData($with_relations,true);
        $old_values = $old_data->diff($collection2);
        $new_values = $collection2->diff($old_data);

        self::createLog('update', $model->getLogModel(),
            $old_values, $new_values,
            get_class($model),
            $model->getKey()
        );
    }

    /**
     * @param ILogable|Model $model
     */
    public static function deleteAction($model)
    {
        self::createLog('delete', $model->getLogModel(),
            $model->getLogData(true,false), null,
            get_class($model),
            $model->getKey()
        );
    }

    /**
     * @param Collection $get
     * @return Collection
     */
    public function map($get)
    {
        return $get->map(function ($item) {
//            $model = $item['model'];
//            if (!is_null($model)){
//                $item['model'] = Translate::translateAttribute($model);
//            }
            $item['attributes'] = json_decode($item['attributes']);
            return $item;
        });
    }
}
