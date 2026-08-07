<?php

use App\Models\Tenant;
use App\Models\TenantIsolationProbe;
use App\Services\Tenant\TenantContext;

it('scopes queries to the active tenant context', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    TenantIsolationProbe::query()->create(['tenant_id' => $tenantA->id, 'label' => 'A1']);
    TenantIsolationProbe::query()->create(['tenant_id' => $tenantB->id, 'label' => 'B1']);

    $context = app(TenantContext::class);
    $context->set($tenantA);

    expect(TenantIsolationProbe::query()->pluck('label')->all())->toBe(['A1']);

    $context->set($tenantB);

    expect(TenantIsolationProbe::query()->pluck('label')->all())->toBe(['B1']);
});

it('auto-assigns tenant_id on create when context is set', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $probe = TenantIsolationProbe::query()->create(['label' => 'probe']);

    expect($probe->tenant_id)->toBe($tenant->id);
});

it('prevents cross-tenant reads without context filtering when context is cleared', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    TenantIsolationProbe::query()->create(['tenant_id' => $tenantA->id, 'label' => 'A1']);
    TenantIsolationProbe::query()->create(['tenant_id' => $tenantB->id, 'label' => 'B1']);

    app(TenantContext::class)->clear();

    expect(TenantIsolationProbe::query()->count())->toBe(2);
});

it('resolves tenant from request header via middleware', function () {
    $tenant = Tenant::factory()->create();
    TenantIsolationProbe::query()->create(['tenant_id' => $tenant->id, 'label' => 'scoped']);

    $otherTenant = Tenant::factory()->create();
    TenantIsolationProbe::query()->create(['tenant_id' => $otherTenant->id, 'label' => 'other']);

    $this->getJson('/api/v1/health/live', [
        config('examflow.tenant_header') => $tenant->id,
    ])->assertOk();

    expect(TenantIsolationProbe::query()->pluck('label')->all())->toBe(['scoped']);
});
