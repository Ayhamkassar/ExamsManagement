<?php

namespace App\Services\Tenant;

final class TenantCacheKey
{
    public static function for(?string $tenantId, string $resource, string|int|null $id = null): string
    {
        $prefix = config('examflow.cache.tenant_prefix', 'tenant');
        $key = "{$prefix}:{$tenantId}:{$resource}";

        if ($id !== null) {
            $key .= ":{$id}";
        }

        return $key;
    }
}
