<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthContextService;
use App\Services\Auth\AuthenticationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $auth,
        private readonly AuthContextService $context,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->attempt(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device'),
        );

        return match ($result['status']) {
            'authenticated' => ApiResponse::success(
                [
                    'user' => new UserResource($result['user']->loadMissing('tenant')),
                    'token' => $result['token'],
                    ...$this->context->for($result['user']),
                ],
                'Login successful.',
            ),
            'account_not_active' => ApiResponse::error(
                'Your account is not active.',
                Response::HTTP_FORBIDDEN,
            ),
            default => ApiResponse::error('Invalid credentials.', Response::HTTP_UNAUTHORIZED),
        };
    }
}
