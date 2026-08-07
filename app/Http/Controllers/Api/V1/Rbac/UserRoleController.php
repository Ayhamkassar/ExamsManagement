<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Rbac\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\Tenant\TenantContext;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenant,
    ) {}

    public function store(AssignRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('assignRole', $user);

        $role = Role::query()->findOrFail($request->validated('role_id'));
        $user->assignRole($role, $request->validated('tenant_id'));
        $user->loadMissing('roles');

        return ApiResponse::success(['user' => new UserResource($user)], 'Role assigned.');
    }

    public function destroy(Request $request, User $user, Role $role): JsonResponse
    {
        $this->authorize('assignRole', $user);

        $tenantId = $request->query('tenant_id') ?? $this->tenant->id();
        $user->revokeRole($role, $tenantId);
        $user->loadMissing('roles');

        return ApiResponse::success(['user' => new UserResource($user)], 'Role revoked.');
    }
}
