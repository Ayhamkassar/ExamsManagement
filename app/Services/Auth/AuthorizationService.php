<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Collection;

final class AuthorizationService
{
    public function userHasPermission(User $user, string $permission, ?string $tenantId = null): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $this->permissionsFor($user, $tenantId)->contains($permission);
    }

    public function userHasRole(User $user, string $role, ?string $tenantId = null): bool
    {
        if ($user->is_super_admin && $role === 'super_admin') {
            return true;
        }

        return $this->rolesFor($user, $tenantId)->contains($role);
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
        return $user->rolesForTenant($tenantId)->pluck('slug');
    }
}
