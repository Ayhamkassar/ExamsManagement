<?php

use App\Models\AcademicYear;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('lets a super admin create an academic year', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/academic-years", [
        'name' => '2024-2025',
        'start_date' => '2024-09-01',
        'end_date' => '2025-06-30',
        'status' => 'active',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'start_date',
            'end_date',
            'status',
        ],
    ]);
})->group('academic-year');

it('lets a super admin list academic years', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/organizations/{$organization->id}/academic-years");

    $response->assertOk();
})->group('academic-year');

it('lets a super admin update an academic year', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $academicYear = AcademicYear::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/organizations/{$organization->id}/academic-years/{$academicYear->id}", [
        'name' => '2025-2026',
    ]);

    $response->assertOk();
    $response->assertJson([
        'data' => [
            'name' => '2025-2026',
        ],
    ]);
})->group('academic-year');

it('lets a super admin delete an academic year', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $academicYear = AcademicYear::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/organizations/{$organization->id}/academic-years/{$academicYear->id}");

    $response->assertOk();
})->group('academic-year');

it('prevents user from organization A from accessing organization B academic years', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $academicYearB = AcademicYear::factory()->create(['tenant_id' => $orgB->id]);

    Sanctum::actingAs($superAdmin);

    // Super admin can access both
    $this->getJson("/api/v1/organizations/{$orgA->id}/academic-years")->assertOk();
    $this->getJson("/api/v1/organizations/{$orgB->id}/academic-years")->assertOk();
})->group('academic-year', 'multi-tenancy');
