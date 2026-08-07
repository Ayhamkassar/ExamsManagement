<?php

use App\Enums\MembershipStatus;
use App\Models\Membership;
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

it('debug test - check role permissions', function () {
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
    echo 'Owner role ID: '.$ownerRole->id;
    echo ' Permissions count: '.$ownerRole->permissions()->count();
    echo ' Permissions: '.$ownerRole->permissions()->pluck('slug')->implode(', ');
})->skip()->group('debug');

it('debug test - check user permissions', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->create();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

    Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'role_id' => $ownerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    $authService = app(AuthorizationService::class);
    echo 'Has academic_year.view: '.($authService->userHasPermission($user, 'academic_year.view', $organization->id) ? 'YES' : 'NO');
    echo ' Has academic_year.create: '.($authService->userHasPermission($user, 'academic_year.create', $organization->id) ? 'YES' : 'NO');
})->skip()->group('debug');
