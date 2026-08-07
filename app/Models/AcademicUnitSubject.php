<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $academic_unit_id
 * @property string $subject_id
 * @property string $academic_year_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AcademicUnitSubject extends Model
{
    use HasUlids;

    public $timestamps = true;

    protected $fillable = [
        'academic_unit_id',
        'subject_id',
        'academic_year_id',
    ];

    public function academicUnit(): BelongsTo
    {
        return $this->belongsTo(AcademicUnit::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
