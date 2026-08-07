<?php

namespace Database\Seeders;

use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [
            SystemRole::SuperAdmin->value => SystemPermission::cases(),
            SystemRole::OrganizationAdmin->value => [
                SystemPermission::ManageOrganization,
                SystemPermission::ManageUsers,
                SystemPermission::ManageRoles,
                SystemPermission::ViewAuditLogs,
            ],
            SystemRole::Examiner->value => [],
            SystemRole::Reviewer->value => [],
            SystemRole::Student->value => [],
        ];

        foreach ($rolePermissions as $roleSlug => $permissions) {
            $role = Role::query()->updateOrCreate(
                [
                    'slug' => $roleSlug,
                    'tenant_id' => null,
                ],
                [
                    'name' => str($roleSlug)->headline()->toString(),
                    'description' => 'System role definition for '.$roleSlug,
                    'is_system' => true,
                ],
            );

            $permissionIds = collect($permissions)
                ->map(fn (SystemPermission $permission) => Permission::query()->where('slug', $permission->value)->value('id'))
                ->filter()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
