<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operation completed successfully.',
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = self::transformData($data);
        }

        return response()->json($payload, $status);
    }

    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully.',
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public static function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function validationError(array $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    private static function transformData(mixed $data): mixed
    {
        // ResourceCollection extends JsonResource, so a single check covers both.
        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        return $data;
    }
}
