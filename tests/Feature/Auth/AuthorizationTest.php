<?php

use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\AuthorizationService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('grants super admin all permissions', function () {
    $user = User::factory()->superAdmin()->create();
    $service = app(AuthorizationService::class);

    expect($service->userHasPermission($user, SystemPermission::ManagePlatform->value))->toBeTrue();
});

it('checks organization admin permissions within tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $role = Role::query()->where('slug', SystemRole::OrganizationAdmin->value)->firstOrFail();
    $user->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

    $service = app(AuthorizationService::class);

    expect($service->userHasPermission($user, SystemPermission::ManageOrganization->value, $tenant->id))->toBeTrue()
        ->and($service->userHasPermission($user, SystemPermission::ManagePlatform->value, $tenant->id))->toBeFalse();
});

it('evaluates tenant policy for organization admin', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $role = Role::query()->where('slug', SystemRole::OrganizationAdmin->value)->firstOrFail();
    $user->roles()->attach($role->id, ['tenant_id' => $tenant->id]);

    expect($this->actingAs($user)->get('/')->status())->not->toBe(500)
        ->and($user->can('view', $tenant))->toBeTrue();
});

it('denies cross-tenant policy access', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenantA->id]);
    $role = Role::query()->where('slug', SystemRole::OrganizationAdmin->value)->firstOrFail();
    $user->roles()->attach($role->id, ['tenant_id' => $tenantA->id]);

    $this->actingAs($user);

    expect($user->can('view', $tenantB))->toBeFalse();
});
