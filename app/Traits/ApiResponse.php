<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a successful JSON response.
     */
    protected function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        $response = ['data' => $data];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a created (201) JSON response.
     */
    protected function created(mixed $data = null, array $meta = []): JsonResponse
    {
        return $this->success($data, $meta, 201);
    }

    /**
     * Return a no-content (204) response.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $code, string $message, array $details = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }
}
