<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\PersonalAccessToken;
use App\Services\Auth\SecurityEventLogger;
use App\Services\Auth\TokenService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    public function __construct(
        private readonly TokenService $tokens,
        private readonly SecurityEventLogger $events,
    ) {}

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->tokens->revokeCurrent($request, $user);
        $this->events->loggedOut($user);

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->tokens->revokeAll($user);
        $this->events->record('logout_all', $user);

        return ApiResponse::success(null, 'Logged out from all devices.');
    }

    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()->getKey();

        $sessions = $this->tokens->list($user)->map(
            fn (PersonalAccessToken $token) => [
                'id' => $token->id,
                'name' => $token->name,
                'device' => $token->device,
                'ip_address' => $token->ip_address,
                'user_agent' => $token->user_agent,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'created_at' => $token->created_at?->toISOString(),
                'is_current' => $token->id === $currentTokenId,
            ],
        );

        return ApiResponse::success(['sessions' => $sessions->values()]);
    }

    public function revokeSession(Request $request, PersonalAccessToken $session): JsonResponse
    {
        $user = $request->user();

        if ((string) $session->tokenable_id !== (string) $user->id) {
            return ApiResponse::error('Session not found.', Response::HTTP_NOT_FOUND);
        }

        $session->delete();
        $this->events->tokenRevoked($user, ['token_id' => $session->id]);

        return ApiResponse::success(null, 'Session revoked.');
    }
}
