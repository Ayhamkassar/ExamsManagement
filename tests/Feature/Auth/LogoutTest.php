<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('web')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($user->tokens()->count())->toBe(0);

    // Reset the in-process guard cache so the next request re-authenticates.
    Auth::forgetGuards();

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
})->group('auth');

it('logs out from all devices', function () {
    $user = User::factory()->create();
    $first = $user->createToken('web')->plainTextToken;
    $second = $user->createToken('mobile')->plainTextToken;

    $this->withToken($first)
        ->postJson('/api/v1/auth/logout-all')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);

    Auth::forgetGuards();

    $this->withToken($second)->getJson('/api/v1/auth/me')->assertUnauthorized();
})->group('auth');

it('lists active sessions', function () {
    $user = User::factory()->create();
    $user->createToken('web')->plainTextToken;
    $current = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($current)
        ->getJson('/api/v1/auth/sessions')
        ->assertOk();

    expect(count($response->json('data.sessions')))->toBe(2);
})->group('auth');

it('revokes a specific session', function () {
    $user = User::factory()->create();
    $current = $user->createToken('web')->plainTextToken;
    $other = $user->createToken('mobile');
    $otherId = $other->accessToken->id;

    $this->withToken($current)
        ->deleteJson("/api/v1/auth/sessions/{$otherId}")
        ->assertOk();

    expect($user->tokens()->count())->toBe(1);
})->group('auth');

it('cannot revoke another users session', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $token = $alice->createToken('web')->plainTextToken;
    $bobsToken = $bob->createToken('mobile');

    $this->withToken($token)
        ->deleteJson("/api/v1/auth/sessions/{$bobsToken->accessToken->id}")
        ->assertNotFound();

    expect($bob->tokens()->count())->toBe(1);
})->group('auth');
