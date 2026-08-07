<?php

use App\Enums\MembershipStatus;
use App\Models\Membership;
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

it('prevents user from organization A from accessing organization B', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Add userA to orgA only
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
    Membership::create([
        'organization_id' => $orgA->id,
        'user_id' => $userA->id,
        'role_id' => $ownerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    // Add userB to orgB only
    Membership::create([
        'organization_id' => $orgB->id,
        'user_id' => $userB->id,
        'role_id' => $ownerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($userA);

    // UserA can access orgA
    $this->getJson("/api/v1/organizations/{$orgA->id}")->assertOk();

    // UserA cannot access orgB
    $this->getJson("/api/v1/organizations/{$orgB->id}")->assertForbidden();

    // UserA cannot see orgB members
    $this->getJson("/api/v1/organizations/{$orgB->id}/members")->assertForbidden();
})->group('multi-tenancy');

it('allows super admin to access any organization', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();

    $superAdmin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($superAdmin);

    $this->getJson("/api/v1/organizations/{$orgA->id}")->assertOk();
    $this->getJson("/api/v1/organizations/{$orgB->id}")->assertOk();
})->group('multi-tenancy');

it('allows user to belong to multiple organizations', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();

    $user = User::factory()->create();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    Membership::create([
        'organization_id' => $orgA->id,
        'user_id' => $user->id,
        'role_id' => $ownerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Membership::create([
        'organization_id' => $orgB->id,
        'user_id' => $user->id,
        'role_id' => $adminRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $this->getJson("/api/v1/organizations/{$orgA->id}")->assertOk();
    $this->getJson("/api/v1/organizations/{$orgB->id}")->assertOk();
    expect($user->memberships()->count())->toBe(2);
})->group('multi-tenancy');
