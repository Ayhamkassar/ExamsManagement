<?php

namespace App\Exceptions;

use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public function render(Request $request, Throwable $exception): ?Response
    {
        if (! $this->shouldRenderAsApi($request)) {
            return null;
        }

        $requestId = $request->attributes->get('request_id');

        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError(
                $exception->errors(),
                'Validation failed.',
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Forbidden.',
                Response::HTTP_FORBIDDEN,
            );
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return ApiResponse::error(
                'Resource not found.',
                Response::HTTP_NOT_FOUND,
            );
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                'Method not allowed.',
                Response::HTTP_METHOD_NOT_ALLOWED,
            );
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return ApiResponse::error(
                'Too many requests.',
                Response::HTTP_TOO_MANY_REQUESTS,
            );
        }

        if ($exception instanceof HttpException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Request could not be processed.',
                $exception->getStatusCode(),
            );
        }

        $this->logUnexpectedException($exception, $requestId);

        $message = config('app.debug')
            ? $exception->getMessage()
            : 'An unexpected error occurred.';

        return ApiResponse::error($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function shouldRenderAsApi(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function logUnexpectedException(Throwable $exception, ?string $requestId): void
    {
        Log::error('Unhandled API exception', [
            'request_id' => $requestId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
