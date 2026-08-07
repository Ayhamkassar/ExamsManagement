<?php

namespace App\Models;

use App\Enums\AcademicYearStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\AcademicYearFactory;
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
 * @property string $name
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property AcademicYearStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => AcademicYearStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function academicUnits(): HasMany
    {
        return $this->hasMany(AcademicUnit::class);
    }

    public function academicUnitSubjects(): HasMany
    {
        return $this->hasMany(AcademicUnitSubject::class);
    }
}
