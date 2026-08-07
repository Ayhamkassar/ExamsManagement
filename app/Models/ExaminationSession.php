<?php

namespace App\Models;

use App\Enums\ExaminationSessionStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ExaminationSessionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $examination_id
 * @property string $session_code
 * @property Carbon $scheduled_start_at
 * @property Carbon $scheduled_end_at
 * @property string $timezone
 * @property string|null $location_name
 * @property array<string, mixed>|null $location_metadata
 * @property ExaminationSessionStatus $status
 * @property int|null $capacity
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ExaminationSession extends Model
{
    /** @use HasFactory<ExaminationSessionFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'examination_id',
        'session_code',
        'scheduled_start_at',
        'scheduled_end_at',
        'timezone',
        'location_name',
        'location_metadata',
        'status',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'location_metadata' => 'array',
            'status' => ExaminationSessionStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
