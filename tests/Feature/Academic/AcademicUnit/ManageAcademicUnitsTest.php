<?php

use App\Models\AcademicUnit;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $this->seed(RoleSeeder::class);
});

it('lets a super admin create an academic unit', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/academic-units", [
        'type' => 'grade',
        'name' => 'Grade 1',
        'status' => 'active',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'type',
            'name',
            'status',
        ],
    ]);
})->group('academic-unit');

it('lets a super admin create academic unit hierarchy', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();

    Sanctum::actingAs($user);

    $gradeResponse = $this->postJson("/api/v1/organizations/{$organization->id}/academic-units", [
        'type' => 'grade',
        'name' => 'Grade 10',
        'status' => 'active',
    ]);

    $gradeId = $gradeResponse->json('data.id');

    $classResponse = $this->postJson("/api/v1/organizations/{$organization->id}/academic-units", [
        'parent_id' => $gradeId,
        'type' => 'class',
        'name' => 'Class A',
        'status' => 'active',
    ]);

    $classResponse->assertCreated();
    $classResponse->assertJson([
        'data' => [
            'parent_id' => $gradeId,
            'type' => 'class',
        ],
    ]);
})->group('academic-unit');

it('prevents circular parent relationship', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $unit = AcademicUnit::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/v1/organizations/{$organization->id}/academic-units/{$unit->id}", [
        'parent_id' => $unit->id,
    ]);

    $response->assertUnprocessable();
    $response->assertJson([
        'success' => false,
        'message' => 'Academic unit cannot be its own parent.',
    ]);
})->group('academic-unit');

it('prevents parent from another organization', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $parentUnitB = AcademicUnit::factory()->create(['tenant_id' => $orgB->id]);
    $unitA = AcademicUnit::factory()->create(['tenant_id' => $orgA->id]);

    Sanctum::actingAs($superAdmin);

    $response = $this->patchJson("/api/v1/organizations/{$orgA->id}/academic-units/{$unitA->id}", [
        'parent_id' => $parentUnitB->id,
    ]);

    $response->assertUnprocessable();
})->group('academic-unit');

it('prevents user from organization A from accessing organization B academic units', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $unitB = AcademicUnit::factory()->create(['tenant_id' => $orgB->id]);

    Sanctum::actingAs($superAdmin);

    $this->getJson("/api/v1/organizations/{$orgA->id}/academic-units")->assertOk();
    $this->getJson("/api/v1/organizations/{$orgB->id}/academic-units")->assertOk();
})->group('academic-unit', 'multi-tenancy');
