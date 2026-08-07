<?php

namespace App\Models;

use App\Enums\AcademicUnitType;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\AcademicUnitFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $parent_id
 * @property AcademicUnitType $type
 * @property string $name
 * @property string|null $code
 * @property array<string, mixed>|null $metadata
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class AcademicUnit extends Model
{
    /** @use HasFactory<AcademicUnitFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'type',
        'name',
        'code',
        'metadata',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => AcademicUnitType::class,
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AcademicUnit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AcademicUnit::class, 'parent_id');
    }

    public function academicYearSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'academic_unit_subject')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->academicYearSubjects();
    }
}
