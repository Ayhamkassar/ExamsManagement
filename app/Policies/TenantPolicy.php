<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\AuthorizationService;

class TenantPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->is_super_admin
            || $this->authorization->userHasPermission($user, 'tenants.manage');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->tenant_id === $tenant->id
            && $this->authorization->userHasPermission($user, 'organization.manage', $tenant->id);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->tenant_id === $tenant->id
            && $this->authorization->userHasPermission($user, 'organization.manage', $tenant->id);
    }
}
