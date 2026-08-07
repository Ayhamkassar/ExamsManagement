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
                SystemPermission::AcademicYearCreate,
                SystemPermission::AcademicYearView,
                SystemPermission::AcademicYearUpdate,
                SystemPermission::AcademicYearDelete,
                SystemPermission::AcademicUnitCreate,
                SystemPermission::AcademicUnitView,
                SystemPermission::AcademicUnitUpdate,
                SystemPermission::AcademicUnitDelete,
                SystemPermission::SubjectCreate,
                SystemPermission::SubjectView,
                SystemPermission::SubjectUpdate,
                SystemPermission::SubjectDelete,
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

        // Create organization-specific roles with full permissions
        $allPermissions = SystemPermission::cases();
        $academicPermissions = [
            SystemPermission::AcademicYearCreate,
            SystemPermission::AcademicYearView,
            SystemPermission::AcademicYearUpdate,
            SystemPermission::AcademicYearDelete,
            SystemPermission::AcademicUnitCreate,
            SystemPermission::AcademicUnitView,
            SystemPermission::AcademicUnitUpdate,
            SystemPermission::AcademicUnitDelete,
            SystemPermission::SubjectCreate,
            SystemPermission::SubjectView,
            SystemPermission::SubjectUpdate,
            SystemPermission::SubjectDelete,
        ];

        $orgRolePermissions = [
            'owner' => $allPermissions,
            'admin' => array_merge([
                SystemPermission::ManageOrganization,
                SystemPermission::ManageUsers,
                SystemPermission::ManageRoles,
                SystemPermission::ViewAuditLogs,
            ], $academicPermissions),
        ];

        foreach ($orgRolePermissions as $roleSlug => $permissions) {
            $role = Role::query()->updateOrCreate(
                [
                    'slug' => $roleSlug,
                    'tenant_id' => null,
                ],
                [
                    'name' => str($roleSlug)->headline()->toString(),
                    'description' => 'Organization role: '.$roleSlug,
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
