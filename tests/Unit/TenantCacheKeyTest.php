<?php

use App\Services\Tenant\TenantCacheKey;

it('builds tenant aware cache keys', function () {
    expect(TenantCacheKey::for('tenant-1', 'users', 'abc'))
        ->toBe('tenant:tenant-1:users:abc');
});
