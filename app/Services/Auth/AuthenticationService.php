<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class AuthenticationService
{
    public function __construct(
        private readonly TokenService $tokens,
        private readonly SecurityEventLogger $events,
    ) {}

    /**
     * @return array{status: string, user?: User, token?: string}
     */
    public function attempt(string $email, string $password, ?string $device = null): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            $this->events->failedLogin($user, ['reason' => 'invalid_credentials']);

            return ['status' => 'invalid_credentials'];
        }

        if (! $user->canAuthenticate()) {
            $this->events->accountBlocked($user, ['status' => $user->status->value]);

            return ['status' => 'account_not_active', 'user' => $user];
        }

        $token = $this->tokens->issue($user, $device);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->save();

        $this->events->loggedIn($user);

        return ['status' => 'authenticated', 'user' => $user, 'token' => $token];
    }
}
