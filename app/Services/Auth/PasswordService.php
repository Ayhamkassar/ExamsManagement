<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

final class PasswordService
{
    public function __construct(
        private readonly TokenService $tokens,
        private readonly SecurityEventLogger $events,
    ) {}

    /**
     * Request a password reset link. Never reveals whether the email exists.
     */
    public function forgot(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        // Always return whatever the broker says; the response is generic to avoid
        // user enumeration. The reset link is only sent when a user exists.
        Password::broker()->sendResetLink(['email' => $email]);

        if ($user !== null) {
            $this->events->passwordResetRequested($user);
        }
    }

    /**
     * Validate a reset token and persist the new password.
     *
     * @return array{status: string}
     */
    public function reset(string $email, string $token, string $password): array
    {
        $status = Password::broker()->reset(
            ['email' => $email, 'token' => $token, 'password' => $password],
            function (User $user, string $newPassword): void {
                $user->forceFill(['password' => $newPassword])->save();
                $this->tokens->revokeAll($user);
                $this->events->passwordReset($user);
            },
        );

        return ['status' => $status];
    }

    /**
     * Change the authenticated user's password after verifying the current one.
     */
    public function change(
        User $user,
        string $currentPassword,
        string $newPassword,
        ?string $currentTokenId = null,
    ): bool {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->forceFill(['password' => $newPassword])->save();

        if (config('examflow.auth.password.revoke_all_on_change', false)) {
            $this->tokens->revokeAll($user);
        } else {
            $this->tokens->revokeAll($user, $currentTokenId);
        }

        $this->events->passwordChanged($user);

        return true;
    }

    /**
     * Human-readable password broker status for API responses.
     */
    public static function isPasswordReset(string $status): bool
    {
        return $status === PasswordBroker::PASSWORD_RESET;
    }
}
