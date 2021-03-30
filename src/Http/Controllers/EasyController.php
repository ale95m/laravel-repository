<?php

namespace Easy\Http\Controllers;

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
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
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
                    ->where(function ($query) use ($field) {
                        return $this->uniqueConditions($query, $field);
                    })]);
            }
            return SendResponse::successData($this->repository->create($request->all()));
        } catch (ValidationException $e) {
            $translate_route = 'validation.attributes.' . $last_check;
            $attribute_name = trans($translate_route);
            return SendResponse::error(trans('validation.unique', ['attribute' => $attribute_name != $translate_route ? $attribute_name : $last_check]));
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
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
        return SendResponse::successData($this->getModel($model));
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
                    ->where(function ($query) use ($field) {
                        return $this->uniqueConditions($query, $field);
                    })->ignore($model)]);
            }
            return SendResponse::successData($this->repository->update($model, $request->all()));
        } catch (ValidationException $e) {
            $translate_route = 'validation.attributes.' . $last_check;
            $attribute_name = trans($translate_route);
            return SendResponse::error(trans('validation.unique', ['attribute' => $attribute_name != $translate_route ? $attribute_name : $last_check]));
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
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
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
        }

    }

    public function getLogs(Request $request, $id)
    {
        $model = $this->getModel($id);
        try {
            return SendResponse::successData($this->repository->getLogs($model, $request->all()));
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
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
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return Model
     */
    private function getModel($id)
    {
        if (is_numeric($id)) {
            return $this->repository->findOrFail($id);
        } else {
            throw new ModelNotFoundException(trans('exceptions.not_found.model'));
        }
    }

    /**
     * @param Builder $query
     * @param $field
     * @return Builder
     */
    protected function uniqueConditions($query, $field)
    {
        return $query->whereNotNull($field);
    }
}
