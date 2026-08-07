<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Rbac\CreateRoleRequest;
use App\Http\Requests\Rbac\SyncRolePermissionsRequest;
use App\Http\Requests\Rbac\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        return ApiResponse::success(['roles' => RoleResource::collection($roles)]);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::query()->create($request->validated());
        $role->loadMissing('permissions');

        return ApiResponse::created(['role' => new RoleResource($role)], 'Role created.');
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $role->loadMissing('permissions');

        return ApiResponse::success(['role' => new RoleResource($role)]);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $role->update($request->safe()->only(['name', 'description']));
        $role->loadMissing('permissions');

        return ApiResponse::success(['role' => new RoleResource($role)], 'Role updated.');
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        return ApiResponse::success(null, 'Role deleted.');
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $this->authorize('syncPermissions', $role);

        $role->permissions()->sync($request->validated('permission_ids'));
        $role->loadMissing('permissions');

        return ApiResponse::success(['role' => new RoleResource($role)], 'Permissions updated.');
    }
}
