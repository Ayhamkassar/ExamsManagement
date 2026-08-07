<?php

namespace App\Policies;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function view(User $user, Membership $membership): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->id === $membership->user_id
            || $user->memberships()
                ->where('organization_id', $membership->organization_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin']);
                })
                ->exists();
    }

    public function update(User $user, Membership $membership): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        // Users can update their own membership (accept invite, etc.)
        if ($user->id === $membership->user_id) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $membership->organization_id)
            ->where('status', MembershipStatus::Active)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin']);
            })
            ->exists();
    }

    public function delete(User $user, Membership $membership): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        // Users can remove themselves
        if ($user->id === $membership->user_id) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $membership->organization_id)
            ->where('status', MembershipStatus::Active)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['owner', 'admin']);
            })
            ->exists();
    }
}
