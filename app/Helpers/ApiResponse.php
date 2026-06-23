<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true];
        if ($message !== null) $payload['message'] = $message;
        if ($data !== null)    $payload['data']    = $data;
        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    public static function paginated(array $data, int $total, int $page, int $perPage): JsonResponse
    {
        return response()->json([
            'success'  => true,
            'data'     => $data,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }
}
