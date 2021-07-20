<?php

namespace Easy\Http\Controllers;

use Easy\Http\Requests\PaginateRequest;
use Easy\Http\Responses\SendResponse;
use Easy\Repositories\LogRepository;

class LogController extends EasyController
{
    public function __construct(LogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function paginate(PaginateRequest $request, Builder|string $search = 'search'): JsonResponse
    {
        $per_page = $request['itemsPerPage'] ?? 20;
        $current_page = $request['page'] ?? 1;
        try {
            $paginator = $this->repository->search($request->all())
                ->paginate($per_page, ['*'], 'page', $current_page);
            $logs = $this->repository->map($paginator->items());
            return SendResponse::successLogsPagination($paginator, $logs);
        } catch (\Exception $e) {
            return SendResponse::error($e->getMessage());
        }
    }
}
