<?php

namespace App\Policies\Academic;

use App\Enums\MembershipStatus;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\Auth\AuthorizationService;

class AcademicYearPolicy
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

    public function view(User $user, AcademicYear $academicYear): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $academicYear->tenant_id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }

    public function create(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_year.create');
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_year.update', $academicYear->tenant_id)
            && $user->memberships()
                ->where('organization_id', $academicYear->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->authorization->userHasPermission($user, 'academic_year.delete', $academicYear->tenant_id)
            && $user->memberships()
                ->where('organization_id', $academicYear->tenant_id)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', function ($query) {
                    $query->whereIn('slug', ['owner', 'admin', 'organization_admin']);
                })
                ->exists();
    }
}
