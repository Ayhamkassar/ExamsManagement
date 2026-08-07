<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rate limits repeated login attempts', function () {
    $user = User::factory()->create(['email' => 'rate-limited@example.com']);

    // Allow up to the configured limit (default 5) of failed attempts.
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'rate-limited@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    // The 6th request within the minute must be throttled.
    $this->postJson('/api/v1/auth/login', [
        'email' => 'rate-limited@example.com',
        'password' => 'password',
    ])->assertStatus(429);
})->group('auth');

it('blocks unauthenticated access to protected routes', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    $this->getJson('/api/v1/roles')->assertUnauthorized();
    $this->getJson('/api/v1/permissions')->assertUnauthorized();
})->group('auth');

it('blocks an authenticated user without permission from admin routes', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/roles')->assertForbidden();
})->group('auth');
