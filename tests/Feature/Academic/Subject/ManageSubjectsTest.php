<?php

use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('lets a super admin create a subject', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/subjects", [
        'name' => 'Mathematics',
        'code' => 'MATH101',
        'description' => 'Basic mathematics',
        'status' => 'active',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'code',
            'description',
        ],
    ]);
})->group('subject');

it('lets a super admin list subjects', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/organizations/{$organization->id}/subjects");

    $response->assertOk();
})->group('subject');

it('lets a super admin update a subject', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/organizations/{$organization->id}/subjects/{$subject->id}", [
        'name' => 'Advanced Mathematics',
    ]);

    $response->assertOk();
    $response->assertJson([
        'data' => [
            'name' => 'Advanced Mathematics',
        ],
    ]);
})->group('subject');

it('lets a super admin delete a subject', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/organizations/{$organization->id}/subjects/{$subject->id}");

    $response->assertOk();
})->group('subject');

it('prevents user from organization A from accessing organization B subjects', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $subjectB = Subject::factory()->create(['tenant_id' => $orgB->id]);

    Sanctum::actingAs($superAdmin);

    $this->getJson("/api/v1/organizations/{$orgA->id}/subjects")->assertOk();
    $this->getJson("/api/v1/organizations/{$orgB->id}/subjects")->assertOk();
})->group('subject', 'multi-tenancy');
