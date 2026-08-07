<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly SecurityEventLogger $events,
    ) {}

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        if (! $request->hasValidSignatureWrapper() || ! $request->userMatchesHash()) {
            return ApiResponse::error('Invalid or expired verification link.', Response::HTTP_BAD_REQUEST);
        }

        $user = User::query()->findOrFail($request->query('id'));

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email already verified.');
        }

        $user->markEmailAsVerified();
        $this->events->emailVerified($user);

        return ApiResponse::success(null, 'Email verified successfully.');
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::error('Email already verified.', Response::HTTP_BAD_REQUEST);
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'Verification email sent.');
    }
}
