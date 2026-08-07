<?php

namespace App\Http\Middleware;

use App\Support\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorizes the authenticated user against a required permission via the
 * 'permission' Gate. Server-side only — never trusts client-supplied claims.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        }

        foreach ($permissions as $permission) {
            if ($user->can('permission', $permission)) {
                return $next($request);
            }
        }

        return ApiResponse::error('You are not authorized to perform this action.', Response::HTTP_FORBIDDEN);
    }
}
