<?php

namespace App\Policies;

use App\Enums\MembershipStatus;
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

        // Backward: check user's direct tenant_id
        if ($user->tenant_id === $tenant->id
            && $this->authorization->userHasPermission($user, 'organization.manage', $tenant->id)) {
            return true;
        }

        // New: check membership
        return $user->memberships()
            ->where('organization_id', $tenant->id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        // Backward: check user's direct tenant_id
        if ($user->tenant_id === $tenant->id
            && $this->authorization->userHasPermission($user, 'organization.manage', $tenant->id)) {
            return true;
        }

        // New: check membership role
        return $user->memberships()
            ->where('organization_id', $tenant->id)
            ->where('status', MembershipStatus::Active)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
            })
            ->exists();
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        // Only the owner can delete
        return $user->memberships()
            ->where('organization_id', $tenant->id)
            ->where('status', MembershipStatus::Active)
            ->whereHas('role', fn ($query) => $query->where('slug', 'owner'))
            ->exists();
    }

    public function manageMembers(User $user, Tenant $tenant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        // Backward: check user's direct tenant_id
        if ($user->tenant_id === $tenant->id
            && $this->authorization->userHasPermission($user, 'organization.manage', $tenant->id)) {
            return true;
        }

        // New: check membership role
        return $user->memberships()
            ->where('organization_id', $tenant->id)
            ->where('status', MembershipStatus::Active)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
            })
            ->exists();
    }
}
