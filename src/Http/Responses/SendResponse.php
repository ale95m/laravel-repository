<?php


namespace Easy\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

class SendResponse
{

    /**
     * @param string $message
     * @return JsonResponse
     */
    public static function success(string $message = 'OK')
    {

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public static function error(string $message, int $status = 200, ?array $data = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        if (isset($data)) {
            $response['data'] = config('easy.json_numeric_check') ? json_encode($data, JSON_NUMERIC_CHECK) : json_encode($data);
        }
        return response()->json($response, $status);
    }

    /**
     * @param string $message
     * @param $data
     * @return JsonResponse
     */
    public static function successData($data, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
//            'data' => config('easy.json_numeric_check') ? json_encode($data, JSON_NUMERIC_CHECK) : json_encode($data)
            'data' => $data
        ]);
    }

    public static function successPagination(LengthAwarePaginator $pagination, $totals = null, string $message = 'OK'): JsonResponse
    {
        $items_per_page_field = config('easy.output.pagination.items_per_page', 'itemsPerPage');
        $current_page_field = config('easy.output.pagination.current_page', 'page');
        $items_length_field = config('easy.output.pagination.items_length', 'itemsLength');
        $page_count_field = config('easy.output.pagination.page_count', 'pageCount');
        return self::successData([
            'current' => $pagination->items(),
            'totals' => $totals,
            'pagination' => [
//                'total' => $pagination->total(),
//                'per_page' => $pagination->perPage(),
//                'current_page' => $pagination->currentPage(),
//                'last_page' => $pagination->lastPage()
                $items_length_field => $pagination->total(),
                $items_per_page_field => $pagination->perPage(),
                $current_page_field => $pagination->currentPage(),
                $page_count_field => $pagination->lastPage()
            ]
        ], $message);
    }

    public static function successSimplePagination(Paginator $pagination, string $message = 'OK'): JsonResponse
    {
        $items_per_page_field = config('easy.output.pagination.items_per_page', 'itemsPerPage');
        $current_page_field = config('easy.output.pagination.current_page', 'page');
        return self::successData([
            'current' => $pagination->items(),
            'pagination' => [
                $items_per_page_field => $pagination->perPage(),
                $current_page_field => $pagination->currentPage(),
            ]
        ], $message);
    }

    public static function successLogsPagination(LengthAwarePaginator $pagination, $logs, string $message = 'OK'): JsonResponse
    {
        return self::successData([
            'current' => $logs,
            'pagination' => [
//                'total' => $pagination->total(),
//                'per_page' => $pagination->perPage(),
//                'current_page' => $pagination->currentPage(),
//                'last_page' => $pagination->lastPage()
                'itemsLength' => $pagination->total(),
                'itemsPerPage' => $pagination->perPage(),
                'page' => $pagination->currentPage(),
                'pageCount' => $pagination->lastPage()
            ]
        ], $message);
    }
}
