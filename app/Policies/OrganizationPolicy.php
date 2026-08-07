<?php

namespace App\Policies;

use App\Enums\MembershipStatus;
use App\Models\Tenant;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Tenant $organization): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $organization->id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function update(User $user, Tenant $organization): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $organization->id)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin']);
            })
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function delete(User $user, Tenant $organization): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $organization->id)
            ->whereHas('role', fn ($query) => $query->where('slug', 'owner'))
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function manageMembers(User $user, Tenant $organization): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $organization->id)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin']);
            })
            ->where('status', MembershipStatus::Active)
            ->exists();
    }
}
