<?php

use App\Models\User;

it('logs in and returns user, token and context', function () {
    $user = User::factory()->create(['email' => 'login@example.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => ['user', 'token', 'roles', 'permissions'],
        ]);

    expect($response->json('data.user.email'))->toBe('login@example.com')
        ->and($response->json('data.token'))->not->toBeNull();
})->group('auth');

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'wrong@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'not-the-password',
    ])->assertUnauthorized();
})->group('auth');

it('blocks a suspended user from logging in', function () {
    User::factory()->suspended()->create(['email' => 'suspended@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'suspended@example.com',
        'password' => 'password',
    ])->assertForbidden();
})->group('auth');

it('blocks an inactive user from logging in', function () {
    User::factory()->inactive()->create(['email' => 'inactive@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ])->assertForbidden();
})->group('auth');

it('does not expose the password hash in the login response', function () {
    $user = User::factory()->create(['email' => 'no-leak@example.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'no-leak@example.com',
        'password' => 'password',
    ]);

    expect($response->getContent())->not->toContain('$2y$')
        ->and($response->json('data.user'))->not->toHaveKey('password');
})->group('auth');

it('updates last login metadata on success', function () {
    $user = User::factory()->create(['email' => 'meta@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'meta@example.com',
        'password' => 'password',
    ])->assertOk();

    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and($user->fresh()->last_login_ip)->toBe('127.0.0.1');
})->group('auth');
