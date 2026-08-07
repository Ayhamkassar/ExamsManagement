<?php

namespace App\Models;

use App\Enums\AuditEventType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationAudit extends Model
{
    use HasUlids;

    protected $table = 'organization_audit_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'event',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event' => AuditEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'organization_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
