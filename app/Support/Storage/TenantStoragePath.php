<?php

namespace App\Support\Storage;

final class TenantStoragePath
{
    public static function for(?string $tenantId, string ...$segments): string
    {
        $prefix = config('examflow.storage.tenant_prefix', 'tenants');
        $parts = array_merge([$prefix, $tenantId], $segments);

        return implode('/', array_filter($parts, fn (?string $part) => $part !== null && $part !== ''));
    }
}
