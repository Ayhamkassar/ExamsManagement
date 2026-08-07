<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function rolesForTenant(?string $tenantId = null): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Role> $roles */
        $roles = $this->roles()
            ->when(
                $tenantId !== null,
                fn ($query) => $query->where(function ($query) use ($tenantId) {
                    $query->whereNull('roles.tenant_id')
                        ->orWhere('roles.tenant_id', $tenantId);
                })->where(function ($query) use ($tenantId) {
                    $query->whereNull('role_user.tenant_id')
                        ->orWhere('role_user.tenant_id', $tenantId);
                }),
            )
            ->get();

        return $roles;
    }
}
