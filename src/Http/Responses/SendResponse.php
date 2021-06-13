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

    /**
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    public static function error(string $message, int $status = 200)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $status);
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
            'data' => config('easy.json_numeric_check') ? json_encode($data, JSON_NUMERIC_CHECK) : json_encode($data)
        ]);
    }

    public static function successPagination(LengthAwarePaginator $pagination, string $message = 'OK'): JsonResponse
    {
        return self::successData([
            'current' => $pagination->items(),
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

    public static function successSimplePagination(Paginator $pagination, string $message = 'OK'): JsonResponse
    {
        return self::successData([
            'current' => $pagination->items(),
            'pagination' => [
                'itemsPerPage' => $pagination->perPage(),
                'page' => $pagination->currentPage(),
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
