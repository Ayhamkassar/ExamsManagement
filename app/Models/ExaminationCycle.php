<?php

namespace App\Models;

use App\Enums\ExaminationCycleStatus;
use App\Enums\ExaminationCycleType;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ExaminationCycleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $academic_year_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property ExaminationCycleType $type
 * @property ExaminationCycleStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property array<string, mixed>|null $metadata
 * @property string $created_by
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ExaminationCycle extends Model
{
    /** @use HasFactory<ExaminationCycleFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'name',
        'code',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExaminationCycleType::class,
            'status' => ExaminationCycleStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }
}
