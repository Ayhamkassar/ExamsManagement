<?php

namespace App\Http\Middleware;

use App\Services\Tenant\TenantContext;
use App\Support\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->has()) {
            return ApiResponse::error(
                'Tenant context is required for this resource.',
                Response::HTTP_BAD_REQUEST,
            );
        }

        return $next($request);
    }
}
