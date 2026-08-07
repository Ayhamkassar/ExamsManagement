<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends Controller
{
    public function __construct(
        private readonly PasswordService $passwords,
    ) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        // Generic response: never reveals whether the email exists.
        $this->passwords->forgot($request->validated('email'));

        return ApiResponse::success(
            null,
            'If that email is registered, a password reset link has been sent.',
        );
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->passwords->reset(
            $request->validated('email'),
            $request->validated('token'),
            $request->validated('password'),
        );

        if (PasswordService::isPasswordReset($result['status'])) {
            return ApiResponse::success(null, 'Password reset successfully.');
        }

        return ApiResponse::error(
            'This password reset token is invalid or has expired.',
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()->getKey();

        $changed = $this->passwords->change(
            $user,
            $request->validated('current_password'),
            $request->validated('password'),
            $currentTokenId,
        );

        if (! $changed) {
            return ApiResponse::error(
                'The current password is incorrect.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return ApiResponse::success(null, 'Password changed successfully.');
    }
}
