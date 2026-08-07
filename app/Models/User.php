<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string|null $tenant_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property UserStatus $status
 * @property bool $is_super_admin
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property Tenant|null $tenant
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
        'status',
        'is_super_admin',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_super_admin' => 'boolean',
        ];
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

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

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Active API tokens / devices for this user.
     *
     * @return HasMany<PersonalAccessToken, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class, 'tokenable_id');
    }

    /**
     * @return HasMany<SecurityEvent, $this>
     */
    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    // ------------------------------------------------------------------
    // Account status
    // ------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function canAuthenticate(): bool
    {
        return $this->status->canLogin();
    }

    // ------------------------------------------------------------------
    // Roles
    // ------------------------------------------------------------------

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

    public function assignRole(Role|string $role, ?string $tenantId = null): void
    {
        $role = $role instanceof Role ? $role : $this->resolveRole($role);

        $existing = $this->roles()
            ->where('roles.id', $role->id)
            ->wherePivot('tenant_id', $tenantId)
            ->exists();

        if (! $existing) {
            $this->roles()->attach($role->id, ['tenant_id' => $tenantId]);
        }
    }

    public function revokeRole(Role|string $role, ?string $tenantId = null): void
    {
        $role = $role instanceof Role ? $role : $this->resolveRole($role);

        $this->roles()
            ->where('roles.id', $role->id)
            ->wherePivot('tenant_id', $tenantId)
            ->detach();
    }

    public function hasRole(string $role, ?string $tenantId = null): bool
    {
        if ($this->is_super_admin && $role === 'super_admin') {
            return true;
        }

        return $this->rolesForTenant($tenantId)->contains('slug', $role);
    }

    private function resolveRole(string $slug): Role
    {
        /** @var Role $role */
        $role = Role::query()->where('slug', $slug)->firstOrFail();

        return $role;
    }

    // ------------------------------------------------------------------
    // Email verification (MustVerifyEmail)
    // ------------------------------------------------------------------

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): bool
    {
        if ($this->hasVerifiedEmail()) {
            return true;
        }

        return (bool) $this->forceFill(['email_verified_at' => now()])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }
}
