<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Auth\AuthorizationService;

class UserPolicy
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {}

    public function view(User $actor, User $user): bool
    {
        if ($actor->is_super_admin) {
            return true;
        }

        if ($actor->id === $user->id) {
            return true;
        }

        return $actor->tenant_id === $user->tenant_id
            && $this->authorization->userHasPermission($actor, 'users.manage', $actor->tenant_id);
    }

    public function update(User $actor, User $user): bool
    {
        return $this->view($actor, $user);
    }

    public function assignRole(User $actor, User $user): bool
    {
        if ($user->is_super_admin && ! $actor->is_super_admin) {
            return false;
        }

        return $actor->is_super_admin
            || $this->authorization->userHasPermission($actor, 'users.manage', $actor->tenant_id);
    }
}
