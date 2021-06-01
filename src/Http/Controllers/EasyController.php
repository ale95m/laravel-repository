<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Helpers\Translate;
use Easy\Http\Requests\PaginateRequest;
use Easy\Http\Responses\SendResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

abstract class EasyController extends \Illuminate\Routing\Controller
{
    protected $repository;
    protected $uniqueFields = [];
    protected $per_page = 20;
    protected $excel_export = null;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            return SendResponse::successData($this->repository->search($request->all())->get());
        }  catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function baseStore($request)
    {
        $last_check = '';
        try {
            foreach ($this->uniqueFields as $field) {
                $last_check = $field;
                $request->validate([$field => Rule::unique($this->repository->getModel()->getTable())
                    ->where(function ($query) use ($field, $request) {
                        return $this->uniqueConditions($query, $field, $request->all());
                    })]);
            }
            return SendResponse::successData($this->repository->create($request->all()));
        } catch (ValidationException $e) {
            return SendResponse::error(trans('validation.unique', ['attribute' => Translate::translateAttribute($last_check)]));
        }  catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $model
     * @return JsonResponse
     */
    public function Show($model)
    {
        return SendResponse::successData($this->getModel($model, false));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $model
     * @return JsonResponse
     */
    protected function baseUpdate($request, $model)
    {
        $last_check = '';
        try {
            foreach ($this->uniqueFields as $field) {
                $last_check = $field;
                $request->validate([$field => Rule::unique($this->repository->getModel()->getTable())
                    ->where(function ($query) use ($field, $request) {
                        return $this->uniqueConditions($query, $field, $request->all());
                    })->ignore($model)]);
            }
            return SendResponse::successData($this->repository->update($model, $request->all()));
        } catch (ValidationException $e) {
            return SendResponse::error(trans('validation.unique', ['attribute' => Translate::translateAttribute($last_check)]));
        } catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $model = $this->getModel($id);
        try {
            return SendResponse::successData($this->repository->delete($model));
        }  catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    public function getLogs(PaginateRequest $request, $id)
    {
        $model = $this->getModel($id);
        $per_page = $request['itemsPerPage'] ?? 20;
        $current_page = $request['page'] ?? 1;
        try {
            $logRepository = new LogRepository();
            $paginator = $this->repository->getLogs($model, $request->all())
                ->paginate($per_page, ['*'], 'page', $current_page);
            $logs = $logRepository->map($paginator->items());
            return SendResponse::successLogsPagination($paginator, $logs);
        }  catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    public function paginate(PaginateRequest $request)
    {
        try {
            $per_page = $request['itemsPerPage'] ?? $this->per_page;
            $current_page = $request['page'] ?? 1;
            $simple_pagination = $request['simple_pagination'] ?? false;
            $search = $this->repository->search($request->all());
            /** @var \Illuminate\Database\Query\Builder $search */
            return $simple_pagination
                ? SendResponse::successSimplePagination(
                    $search->simplePaginate($per_page, ['*'], 'page', $current_page)
                )
                : SendResponse::successPagination(
                    $search->paginate($per_page, ['*'], 'page', $current_page)
                );
        }  catch (EasyException $e) {
            return SendResponse::error($e->getMessage());
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                return SendResponse::error($e->getMessage());
            } else {
                abort(500);
            }
        }
    }

    /**
     * @param $id
     * @param bool $clean
     * @return Model
     */
    private function getModel($id, $clean = true)
    {
        if (is_numeric($id)) {
            return $this->repository->findOrFail($id, $clean);
        } else {
            throw new ModelNotFoundException("Not found");
        }
    }

    /**
     * @param Builder $query
     * @param $field
     * @param array $data
     * @return Builder
     */
    protected function uniqueConditions($query, $field, array $data)
    {
        return $query->whereNotNull($field);
    }
}
