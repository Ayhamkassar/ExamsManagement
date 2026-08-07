<?php

namespace App\Models;

use App\Enums\ExaminationStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ExaminationFactory;
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
 * @property string $examination_cycle_id
 * @property string|null $subject_id
 * @property string|null $academic_unit_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $duration_minutes
 * @property int $total_marks
 * @property int|null $passing_marks
 * @property ExaminationStatus $status
 * @property array<string, mixed>|null $configuration
 * @property string $created_by
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Examination extends Model
{
    /** @use HasFactory<ExaminationFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'examination_cycle_id',
        'subject_id',
        'academic_unit_id',
        'name',
        'code',
        'description',
        'duration_minutes',
        'total_marks',
        'passing_marks',
        'status',
        'configuration',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExaminationStatus::class,
            'configuration' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function examinationCycle(): BelongsTo
    {
        return $this->belongsTo(ExaminationCycle::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicUnit(): BelongsTo
    {
        return $this->belongsTo(AcademicUnit::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExaminationSession::class);
    }
}
