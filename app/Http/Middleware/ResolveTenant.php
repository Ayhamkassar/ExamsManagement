<?php

namespace App\Http\Middleware;

use App\Enums\MembershipStatus;
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
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($tenant === null) {
            return ApiResponse::error(
                'Invalid or inactive tenant.',
                Response::HTTP_NOT_FOUND,
            );
        }

        // If user is authenticated, verify they belong to this organization
        $user = $request->user();
        if ($user !== null && ! $user->is_super_admin) {
            $belongs = $user->memberships()
                ->where('organization_id', $tenant->id)
                ->where('status', MembershipStatus::Active)
                ->exists();

            if (! $belongs) {
                return ApiResponse::error(
                    'You do not have access to this organization.',
                    Response::HTTP_FORBIDDEN,
                );
            }
        }

        $this->tenantContext->set($tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
