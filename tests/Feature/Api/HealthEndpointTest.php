<?php

use App\Models\Tenant;

it('returns liveness status', function () {
    $response = $this->getJson('/api/v1/health/live');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'alive',
            ],
        ]);
});

it('returns readiness status with dependency checks', function () {
    $response = $this->getJson('/api/v1/health/ready');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'checks' => [
                    'database',
                ],
                'timestamp',
            ],
        ]);
});

it('includes request id header on health responses', function () {
    $response = $this->getJson('/api/v1/health/live');

    $response->assertHeader(config('examflow.request_id_header', 'X-Request-ID'));
});

it('rejects invalid tenant header', function () {
    $response = $this->getJson('/api/v1/health/live', [
        config('examflow.tenant_header', 'X-Tenant-ID') => 'invalid-tenant-id',
    ]);

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
        ]);
});

it('accepts valid tenant header', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->getJson('/api/v1/health/live', [
        config('examflow.tenant_header', 'X-Tenant-ID') => $tenant->id,
    ]);

    $response->assertOk();
});
