<?php

namespace App\Policies\Academic;

use App\Enums\MembershipStatus;
use App\Models\AcademicUnit;
use App\Models\User;
use App\Services\Auth\AuthorizationService;

class AcademicUnitPolicy
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

    public function view(User $user, AcademicUnit $academicUnit): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $academicUnit->tenant_id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function create(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_unit.create');
    }

    public function update(User $user, AcademicUnit $academicUnit): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_unit.update', $academicUnit->tenant_id)
            && $user->memberships()
                ->where('organization_id', $academicUnit->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }

    public function delete(User $user, AcademicUnit $academicUnit): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_unit.delete', $academicUnit->tenant_id)
            && $user->memberships()
                ->where('organization_id', $academicUnit->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }
}
