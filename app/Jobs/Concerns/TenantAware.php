<?php

namespace App\Jobs\Concerns;

use App\Models\Tenant;
use App\Services\Tenant\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;

trait TenantAware
{
    public ?string $tenantId = null;

    public function withTenant(Tenant|string $tenant): static
    {
        $this->tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this;
    }

    protected function initializeTenantContext(): void
    {
        if ($this->tenantId === null) {
            return;
        }

        $tenant = Tenant::query()->find($this->tenantId);

        if ($tenant !== null) {
            app(TenantContext::class)->set($tenant);
        }
    }

    public function handleTenantAware(): void
    {
        if ($this instanceof ShouldQueue) {
            $this->initializeTenantContext();
        }
    }
}
