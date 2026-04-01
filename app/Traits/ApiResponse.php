<?php

namespace App\Traits;

trait ApiResponse
{
    public function success(
        $status = true,
        $message = '',
        $result = [],
        $code = 200
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => $result,
            'code' => $code
        ], $code);
    }

    public function error(
        $status = false,
        $message = '',
        $result = [],
        $code = 400,
        $is_object = false
    ) {
        if ($is_object) {
            $result = (object)[];
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => $result,
            'code' => $code
        ], $code);
    }

    public function noContentResponse(
        $status = true,
        $message = 'No Content',
        $code = 204
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => (object)[],
            'code' => $code
        ], $code);
    }

    public function unauthorizedResponse(
        $status = false,
        $message = 'Unauthorized',
        $code = 401
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => (object)[],
            'code' => $code
        ], $code);
    }

    public function forbiddenResponse(
        $status = false,
        $message = 'Forbidden',
        $code = 403
    ) {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => (object)[],
            'code' => $code
        ], $code);
    }
}
