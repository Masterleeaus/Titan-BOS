<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Reply — WorkCore AJAX response helper.
 *
 * WorkSuite controllers return JSON via:
 *   return Reply::success('message', $data);
 *   return Reply::error('message', $data);
 *   return Reply::dataOnly($data);
 *
 * This class bridges those calls into standard Laravel JSON responses.
 */
class WorkSuiteReply
{
    public static function success(string $message = '', array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_merge([
            'status'  => 'success',
            'message' => $message,
        ], $data), $status);
    }

    public static function error(string $message = '', array $data = [], int $status = 400): JsonResponse
    {
        return response()->json(array_merge([
            'status'  => 'error',
            'message' => $message,
        ], $data), $status);
    }

    public static function dataOnly(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], 403);
    }
}
