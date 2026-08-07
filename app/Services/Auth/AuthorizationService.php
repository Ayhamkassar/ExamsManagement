<?php

namespace App\Services\Auth;

use App\Enums\MembershipStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

final class AuthorizationService
{
    public function userHasPermission(User $user, string $permission, ?string $tenantId = null): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($tenantId !== null) {
            // Check membership-based permissions first
            $membershipRole = $user->memberships()
                ->where('organization_id', $tenantId)
                ->where('status', MembershipStatus::Active)
                ->with('role.permissions')
                ->first();

            /** @var \App\Models\Membership|null $membershipRole */

            if ($membershipRole !== null && $membershipRole->role !== null) {
                /** @var \App\Models\Role $membershipRoleRole */
                $membershipRoleRole = $membershipRole->role;

                return $membershipRoleRole->permissions
                    ->pluck('slug')
                    ->contains($permission);
            }

            // Fallback to role_user based permissions (backward compatibility)
            return $this->permissionsFor($user, $tenantId)->contains($permission);
        }

        // Global permissions (no tenant context)
        return $this->permissionsFor($user)->contains($permission);
    }

    public function userHasRole(User $user, string $role, ?string $tenantId = null): bool
    {
        if ($user->is_super_admin && $role === 'super_admin') {
            return true;
        }

        if ($tenantId !== null) {
            // Check membership-based role first
            $hasMembershipRole = $user->memberships()
                ->where('organization_id', $tenantId)
                ->where('status', MembershipStatus::Active)
                ->whereHas('role', fn ($query) => $query->where('slug', $role))
                ->exists();

            if ($hasMembershipRole) {
                return true;
            }

            // Fallback to role_user based role (backward compatibility)
            return $user->rolesForTenant($tenantId)->contains('slug', $role);
        }

        return $user->rolesForTenant()->contains('slug', $role);
    }

    /**
     * @return Collection<int, string>
     */
    public function permissionsFor(User $user, ?string $tenantId = null): Collection
    {
        return $user->rolesForTenant($tenantId)
            ->loadMissing('permissions')
            ->flatMap(fn ($role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function rolesFor(User $user, ?string $tenantId = null): Collection
    {
        if ($tenantId !== null) {
            // Combine membership roles and role_user roles
            $membershipRoles = $user->memberships()
                ->where('organization_id', $tenantId)
                ->where('status', MembershipStatus::Active)
                ->with('role')
                ->get()
                ->pluck('role.slug')
                ->filter();

            $legacyRoles = $user->rolesForTenant($tenantId)->pluck('slug');

            return $membershipRoles->merge($legacyRoles)->unique()->values();
        }

        return $user->rolesForTenant()->pluck('slug');
    }

    public function userBelongsToOrganization(User $user, string $organizationId): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->memberships()
            ->where('organization_id', $organizationId)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }
}
