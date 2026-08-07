<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AuthorizationService;

class RolePolicy
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Role $role): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $this->canManage($user);
    }

    public function syncPermissions(User $user, Role $role): bool
    {
        return $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        return $user->is_super_admin
            || $this->authorization->userHasPermission($user, 'roles.manage');
    }
}
