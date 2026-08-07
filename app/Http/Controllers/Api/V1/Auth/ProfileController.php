<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthContextService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuthContextService $context,
    ) {}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('tenant');

        return ApiResponse::success([
            'user' => new UserResource($user),
            ...$this->context->for($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('update', $user);

        $user->update($request->safe()->only(['name', 'phone']));
        $user->loadMissing('tenant');

        return ApiResponse::success([
            'user' => new UserResource($user),
            ...$this->context->for($user),
        ], 'Profile updated successfully.');
    }
}
