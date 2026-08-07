<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Tenant\TenantContext;

/**
 * Builds the roles / permissions / tenant context returned with auth responses.
 * Honors the currently resolved tenant context when present.
 */
final class AuthContextService
{
    public function __construct(
        private readonly AuthorizationService $authorization,
        private readonly TenantContext $tenant,
    ) {}

    /**
     * @return array{roles: list<string>, permissions: list<string>, tenant: array{id: string, name: string}|null}
     */
    public function for(User $user): array
    {
        $tenantId = $this->tenant->id();

        $tenant = null;
        $current = $this->tenant->get();
        if ($current !== null) {
            $tenant = ['id' => $current->id, 'name' => $current->name];
        }

        return [
            'roles' => $this->authorization->rolesFor($user, $tenantId)->values()->all(),
            'permissions' => $this->authorization->permissionsFor($user, $tenantId)->values()->all(),
            'tenant' => $tenant,
        ];
    }
}
