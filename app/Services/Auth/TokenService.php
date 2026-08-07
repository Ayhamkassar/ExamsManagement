<?php

namespace App\Services\Auth;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;

/**
 * Centralises Sanctum token issuance, revocation and device listing so that
 * controllers stay thin and behaviour (naming, abilities, device metadata,
 * expiration) is consistent.
 */
final class TokenService
{
    /**
     * Create a token for the user and return its plain-text value.
     */
    public function issue(User $user, ?string $device = null, ?array $abilities = null): string
    {
        $name = (string) config('examflow.auth.token_name', 'examflow-session');
        $abilities ??= config('examflow.auth.token_abilities', ['*']);
        $expiration = (int) config('examflow.auth.token_expiration_minutes', 0);

        /** @var NewAccessToken $result */
        $result = $user->createToken($name, $abilities, $expiration > 0 ? now()->addMinutes($expiration) : null);

        /** @var PersonalAccessToken $accessToken */
        $accessToken = $result->accessToken;
        $accessToken->forceFill(['device' => $device])->save();

        return $result->plainTextToken;
    }

    public function revokeCurrent(Request $request, User $user): void
    {
        $token = $request->user()?->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }

    public function revokeAll(User $user, ?string $exceptTokenId = null): int
    {
        $query = $user->devices();

        if ($exceptTokenId !== null) {
            $query->where('id', '!=', $exceptTokenId);
        }

        return $query->delete();
    }

    /**
     * @return Collection<int, PersonalAccessToken>
     */
    public function list(User $user): Collection
    {
        return $user->devices()
            ->orderByDesc('last_used_at')
            ->get();
    }
}
