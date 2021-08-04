<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Helpers\Translate;
use Easy\Http\Requests\PaginateRequest;
use Easy\Http\Responses\SendResponse;
use Easy\Repositories\LogRepository;
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
    protected array $uniqueFields = [];
    protected int $per_page = 20;
    protected $excel_export = null;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->repository->search($request->all());
        if ($this->repository->getWithTotals()) {
            $clone = clone $query;
            return SendResponse::successData([
                'current' => $query->get(),
                'totals' => $this->repository->getTotals($clone)
            ]);
        }
        return SendResponse::successData($query->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function baseStore($request): JsonResponse
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
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $model
     * @return JsonResponse
     */
    public function Show($model): JsonResponse
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
    protected function baseUpdate($request, $model): JsonResponse
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
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $model = $this->getModel($id);
        return SendResponse::successData($this->repository->delete($model));
    }

    /**
     * Restore the specified resource if is softdeleted.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $model = $this->getModel($id);
        return SendResponse::successData($this->repository->restore($model));
    }

    public function getLogs(PaginateRequest $request, $id): JsonResponse
    {
        $model = $this->getModel($id);
        $per_page = $request['itemsPerPage'] ?? 20;
        $current_page = $request['page'] ?? 1;
        $logRepository = new LogRepository();
        $paginator = $this->repository->getLogs($model, $request->all())
            ->paginate($per_page, ['*'], 'page', $current_page);
        $logs = $logRepository->map($paginator->items());
        return SendResponse::successLogsPagination($paginator, $logs);
    }

    public function paginate(PaginateRequest $request, string|Builder $search = 'search'): JsonResponse
    {
        $items_per_page_field = config('easy.input.pagination.items_per_page', 'itemsPerPage');
        $current_page_field = config('easy.input.pagination.current_page', 'page');
        $per_page = $request[$items_per_page_field] ?? $this->per_page;
        $current_page = $request[$current_page_field] ?? 1;
//            $simple_pagination = $request['simple_pagination'] ?? false;
        $query = is_string($search)
            ? $this->repository->$search($request->all())
            : $search;
        if ($this->repository->getWithTotals()) {
            $clone = clone $query;
            return SendResponse::successPagination(
                $query->paginate($per_page, ['*'], 'page', $current_page),
                $this->repository->getTotals($clone)
            );
        }
        return SendResponse::successPagination(
            $query->paginate($per_page, ['*'], 'page', $current_page)
        );
    }

    /**
     * @param $id
     * @param bool $clean
     * @return Model
     */
    private function getModel($id, bool $clean = true): Model
    {
        if (is_numeric($id)) {
            return $this->repository->findOrFail($id, $clean);
        } else {
            throw new ModelNotFoundException("Not found");
        }
    }

    /**
     * @param $query
     * @param $field
     * @param array $data
     * @return Builder
     */
    protected function uniqueConditions($query, $field, array $data)
    {
        return $query->whereNotNull($field);
    }
}
