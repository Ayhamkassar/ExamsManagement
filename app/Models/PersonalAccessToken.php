<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Extends Sanctum's token model so each token can carry device/session metadata
 * (IP address, user agent, device label) for session management and auditing.
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'ip_address',
        'user_agent',
        'device',
        'last_used_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (PersonalAccessToken $token): void {
            if ($token->ip_address === null) {
                $token->ip_address = Request::ip();
            }
            if ($token->user_agent === null) {
                $token->user_agent = Request::userAgent();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tokenable_id');
    }
}
