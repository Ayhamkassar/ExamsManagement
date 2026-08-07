<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantContext;
use App\Support\Api\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = config('examflow.tenant_header', 'X-Tenant-ID');
        $tenantId = $request->header($header);

        if ($tenantId === null || $tenantId === '') {
            return $next($request);
        }

        $tenant = Tenant::query()
            ->where('id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($tenant === null) {
            return ApiResponse::error(
                'Invalid or inactive tenant.',
                Response::HTTP_NOT_FOUND,
            );
        }

        $this->tenantContext->set($tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
