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

it('lets an organization owner invite a member', function () {
    $organization = Tenant::factory()->create();
    $owner = User::factory()->create();
    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

    Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $owner->id,
        'role_id' => $ownerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    $newUser = User::factory()->create();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/members/invite", [
        'email' => $newUser->email,
        'role_id' => $adminRole->id,
    ]);

    $response->assertCreated();
    expect(Membership::query()->count())->toBe(2);
})->group('membership');

it('lets an admin update a member role', function () {
    $organization = Tenant::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $reviewerRole = Role::query()->where('slug', 'reviewer')->firstOrFail();

    Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $admin->id,
        'role_id' => $adminRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    $memberMembership = Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $member->id,
        'role_id' => $reviewerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($admin);

    $response = $this->patchJson(
        "/api/v1/organizations/{$organization->id}/members/{$memberMembership->id}",
        ['role_id' => $adminRole->id]
    );

    $response->assertOk();
    expect($memberMembership->fresh()->role_id)->toBe($adminRole->id);
})->group('membership');

it('lets an admin remove a member', function () {
    $organization = Tenant::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $admin->id,
        'role_id' => $adminRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    $memberMembership = Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $member->id,
        'role_id' => $adminRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($admin);

    $this->deleteJson(
        "/api/v1/organizations/{$organization->id}/members/{$memberMembership->id}"
    )->assertOk();

    expect(Membership::query()->find($memberMembership->id))->toBeNull();
})->group('membership');

it('denies member management to non-admins', function () {
    $organization = Tenant::factory()->create();
    $member = User::factory()->create();
    $reviewerRole = Role::query()->where('slug', 'reviewer')->firstOrFail();

    Membership::create([
        'organization_id' => $organization->id,
        'user_id' => $member->id,
        'role_id' => $reviewerRole->id,
        'status' => MembershipStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($member);

    $this->getJson("/api/v1/organizations/{$organization->id}/members")->assertForbidden();
})->group('membership');
