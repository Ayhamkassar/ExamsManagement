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

it('lets a super admin create an organization', function () {
    $admin = User::factory()->superAdmin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/organizations', [
        'name' => 'Test University',
        'slug' => 'test-university-'.uniqid(),
        'type' => 'university',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'slug',
            'type',
            'status',
        ],
    ]);

    expect(Tenant::query()->count())->toBe(1);
    expect(Membership::query()->count())->toBe(1);
})->group('organization');

it('lets an authenticated user create an organization and become owner', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $slug = 'my-school-'.uniqid();
    $response = $this->postJson('/api/v1/organizations', [
        'name' => 'My School',
        'slug' => $slug,
        'type' => 'school',
    ]);

    $response->assertCreated();

    $organization = Tenant::query()->where('slug', $slug)->firstOrFail();
    $membership = Membership::query()->where('organization_id', $organization->id)->firstOrFail();

    expect($membership->user_id)->toBe($user->id);
    expect($membership->status)->toBe(MembershipStatus::Active);

    $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
    expect($membership->role_id)->toBe($ownerRole->id);
})->group('organization');

it('denies organization creation to unauthenticated users', function () {
    $this->postJson('/api/v1/organizations', [
        'name' => 'Test',
        'slug' => 'test-'.uniqid(),
        'type' => 'school',
    ])->assertUnauthorized();
})->group('organization');
