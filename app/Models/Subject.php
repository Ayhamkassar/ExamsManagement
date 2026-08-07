<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use BelongsToTenant, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'metadata',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function academicUnits(): BelongsToMany
    {
        return $this->belongsToMany(AcademicUnit::class, 'academic_unit_subject')
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }
}
