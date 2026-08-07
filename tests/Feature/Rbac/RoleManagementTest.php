<?php

use App\Enums\SystemRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('lets a super admin list roles and permissions', function () {
    $admin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/roles')->assertOk();
    $this->getJson('/api/v1/permissions')->assertOk()->assertJsonStructure(['data' => ['permissions']]);
})->group('rbac');

it('denies role access to a user without roles.manage', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/roles')->assertForbidden();
})->group('rbac');

it('lets an organization admin create and sync a role', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $orgAdminRole = Role::query()->where('slug', SystemRole::OrganizationAdmin->value)->firstOrFail();
    $user->roles()->attach($orgAdminRole->id, ['tenant_id' => $tenant->id]);
    Sanctum::actingAs($user);

    $created = $this->postJson('/api/v1/roles', [
        'name' => 'Custom Supervisor',
        'slug' => 'custom-supervisor',
    ])->assertCreated()->json('data.role');

    $permission = Permission::query()->firstOrFail();

    $this->postJson("/api/v1/roles/{$created['id']}/permissions", [
        'permission_ids' => [$permission->id],
    ])->assertOk();

    expect(Role::query()->find($created['id'])->permissions()->count())->toBe(1);
})->group('rbac');

it('assigns a role to a user', function () {
    $admin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($admin);

    $target = User::factory()->create();
    $role = Role::query()->where('slug', SystemRole::Student->value)->firstOrFail();

    $this->postJson("/api/v1/users/{$target->id}/roles", [
        'role_id' => $role->id,
    ])->assertOk();

    expect($target->fresh()->roles->contains('slug', 'student'))->toBeTrue();
})->group('rbac');

it('cannot delete a system role', function () {
    $admin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($admin);

    $role = Role::query()->where('slug', SystemRole::Student->value)->firstOrFail();

    $this->deleteJson("/api/v1/roles/{$role->id}")->assertForbidden();

    expect(Role::query()->find($role->id))->not->toBeNull();
})->group('rbac');

it('cannot assign roles without users.manage', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $target = User::factory()->create();
    $role = Role::query()->where('slug', SystemRole::Student->value)->firstOrFail();

    $this->postJson("/api/v1/users/{$target->id}/roles", [
        'role_id' => $role->id,
    ])->assertForbidden();

    expect($target->fresh()->roles->count())->toBe(0);
})->group('rbac');
