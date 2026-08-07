<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MembershipStatus;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\CreateOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Membership;
use App\Models\Role;
use App\Models\Tenant;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_super_admin) {
            $organizations = Tenant::query()->paginate();
        } else {
            $organizationIds = $user->memberships()
                ->where('status', MembershipStatus::Active)
                ->pluck('organization_id');

            $organizations = Tenant::query()
                ->whereIn('id', $organizationIds)
                ->paginate();
        }

        return ApiResponse::success(
            OrganizationResource::collection($organizations),
        );
    }

    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_super_admin) {
            // Future: Check subscription limits here
        }

        $validated = $request->validated();
        $validated['created_by'] = $user->id;
        $validated['status'] = TenantStatus::Active;

        $organization = Tenant::create($validated);

        // Create owner membership
        $ownerRole = Role::query()
            ->where('slug', 'owner')
            ->where('tenant_id', null)
            ->first();

        if ($ownerRole === null) {
            $ownerRole = Role::query()
                ->where('slug', 'organization_admin')
                ->where('tenant_id', null)
                ->firstOrFail();
        }

        Membership::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => $ownerRole->id,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $this->securityEventLogger->record(
            'organization.created',
            $user,
            [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'organization_type' => $organization->type instanceof \BackedEnum ? $organization->type->value : $organization->type,
            ],
        );

        return ApiResponse::created(
            new OrganizationResource($organization),
            'Organization created successfully.',
        );
    }

    public function show(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('view', $organization);

        $organization->loadMissing('createdBy');

        return ApiResponse::success(
            new OrganizationResource($organization),
        );
    }

    public function update(UpdateOrganizationRequest $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('update', $organization);

        $organization->update($request->validated());

        $this->securityEventLogger->record(
            'organization.updated',
            $user,
            [
                'organization_id' => $organization->id,
                'changes' => $request->validated(),
            ],
        );

        return ApiResponse::success(
            new OrganizationResource($organization->fresh()),
            'Organization updated successfully.',
        );
    }

    public function destroy(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('delete', $organization);

        // Soft delete
        $organization->delete();

        $this->securityEventLogger->record(
            'organization.deleted',
            $user,
            [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
            ],
        );

        return ApiResponse::success(
            null,
            'Organization deleted successfully.',
        );
    }
}
