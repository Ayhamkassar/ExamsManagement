<?php

namespace App\Models;

use App\Enums\OrganizationType;
use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $legal_name
 * @property \App\Enums\OrganizationType|null $type
 * @property \App\Enums\TenantStatus $status
 * @property string|null $logo_path
 * @property array<string, mixed>|null $settings
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'type',
        'status',
        'logo_path',
        'settings',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'type' => OrganizationType::class,
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'organization_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
