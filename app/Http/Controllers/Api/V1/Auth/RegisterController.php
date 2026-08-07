<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private readonly SecurityEventLogger $events,
    ) {}

    public function store(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'status' => UserStatus::Active,
        ]);

        $this->events->record('registered', $user);

        $user->sendEmailVerificationNotification();

        return ApiResponse::created(
            ['user' => new UserResource($user)],
            'Registration successful. Please verify your email.',
        );
    }
}
