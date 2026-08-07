<?php

namespace App\Policies\Academic;

use App\Enums\MembershipStatus;
use App\Models\Subject;
use App\Models\User;
use App\Services\Auth\AuthorizationService;

class SubjectPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {}

    public function viewAny(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function view(User $user, Subject $subject): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $subject->tenant_id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function create(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'subject.create');
    }

    public function update(User $user, Subject $subject): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'subject.update', $subject->tenant_id)
            && $user->memberships()
                ->where('organization_id', $subject->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }

    public function delete(User $user, Subject $subject): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'subject.delete', $subject->tenant_id)
            && $user->memberships()
                ->where('organization_id', $subject->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }
}
