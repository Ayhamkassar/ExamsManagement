<?php

namespace App\Services\Auth;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\Request;

/**
 * Records authentication/security events (login, logout, password changes,
 * etc.) into the separate, append-only security_events table. This is kept
 * distinct from business audit logs (app/Services/Audit/AuditLogger).
 */
final class SecurityEventLogger
{
    public function record(string $event, ?User $user = null, array $metadata = []): void
    {
        SecurityEvent::query()->create([
            'user_id' => $user?->id,
            'event' => $event,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => array_filter($metadata),
        ]);
    }

    public function loggedIn(User $user, array $metadata = []): void
    {
        $this->record('login_success', $user, $metadata);
    }

    public function failedLogin(?User $user = null, array $metadata = []): void
    {
        $this->record('login_failed', $user, $metadata);
    }

    public function suspiciousLogin(?User $user, array $metadata = []): void
    {
        $this->record('login_suspicious', $user, $metadata);
    }

    public function loggedOut(User $user, array $metadata = []): void
    {
        $this->record('logout', $user, $metadata);
    }

    public function passwordResetRequested(?User $user, array $metadata = []): void
    {
        $this->record('password_reset_requested', $user, $metadata);
    }

    public function passwordReset(User $user, array $metadata = []): void
    {
        $this->record('password_reset', $user, $metadata);
    }

    public function passwordChanged(User $user, array $metadata = []): void
    {
        $this->record('password_changed', $user, $metadata);
    }

    public function emailVerified(User $user, array $metadata = []): void
    {
        $this->record('email_verified', $user, $metadata);
    }

    public function tokenRevoked(User $user, array $metadata = []): void
    {
        $this->record('token_revoked', $user, $metadata);
    }

    public function accountBlocked(?User $user, array $metadata = []): void
    {
        $this->record('login_blocked', $user, $metadata);
    }
}
