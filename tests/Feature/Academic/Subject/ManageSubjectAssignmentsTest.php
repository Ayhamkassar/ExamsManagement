<?php

use App\Models\AcademicUnit;
use App\Models\AcademicYear;
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

it('lets a super admin assign a subject to an academic unit', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $unit = AcademicUnit::factory()->create(['tenant_id' => $organization->id]);
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/academic-units/{$unit->id}/subjects", [
        'subject_id' => $subject->id,
    ]);

    $response->assertCreated();
    $response->assertJson([
        'data' => [
            'id' => $subject->id,
            'name' => $subject->name,
        ],
    ]);
})->group('subject-assignment');

it('lets a super admin assign a subject with academic year', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $unit = AcademicUnit::factory()->create(['tenant_id' => $organization->id]);
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);
    $academicYear = AcademicYear::factory()->create(['tenant_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/organizations/{$organization->id}/academic-units/{$unit->id}/subjects", [
        'subject_id' => $subject->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $response->assertCreated();
})->group('subject-assignment');

it('lets a super admin list subjects assigned to an academic unit', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $unit = AcademicUnit::factory()->create(['tenant_id' => $organization->id]);
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);

    $unit->subjects()->attach($subject->id);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/organizations/{$organization->id}/academic-units/{$unit->id}/subjects");

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
            ],
        ],
    ]);
})->group('subject-assignment');

it('lets a super admin unassign a subject from an academic unit', function () {
    $organization = Tenant::factory()->create();
    $user = User::factory()->superAdmin()->create();
    $unit = AcademicUnit::factory()->create(['tenant_id' => $organization->id]);
    $subject = Subject::factory()->create(['tenant_id' => $organization->id]);

    $unit->subjects()->attach($subject->id);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/organizations/{$organization->id}/academic-units/{$unit->id}/subjects/{$subject->id}");

    $response->assertOk();
    $response->assertJson([
        'message' => 'Subject unassigned successfully.',
    ]);
})->group('subject-assignment');

it('prevents assigning subject from another organization', function () {
    $orgA = Tenant::factory()->create();
    $orgB = Tenant::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $unitA = AcademicUnit::factory()->create(['tenant_id' => $orgA->id]);
    $subjectB = Subject::factory()->create(['tenant_id' => $orgB->id]);

    Sanctum::actingAs($superAdmin);

    $response = $this->postJson("/api/v1/organizations/{$orgA->id}/academic-units/{$unitA->id}/subjects", [
        'subject_id' => $subjectB->id,
    ]);

    $response->assertNotFound();
})->group('subject-assignment');
