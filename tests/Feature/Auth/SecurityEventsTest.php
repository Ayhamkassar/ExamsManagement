<?php

use App\Models\SecurityEvent;
use App\Models\User;

it('records a successful login event', function () {
    $user = User::factory()->create(['email' => 'events@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'events@example.com',
        'password' => 'password',
    ])->assertOk();

    expect(SecurityEvent::query()
        ->where('user_id', $user->id)
        ->where('event', 'login_success')
        ->exists())->toBeTrue();
})->group('auth');

it('records a failed login event', function () {
    $user = User::factory()->create(['email' => 'fail-events@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'fail-events@example.com',
        'password' => 'wrong',
    ])->assertUnauthorized();

    expect(SecurityEvent::query()
        ->where('user_id', $user->id)
        ->where('event', 'login_failed')
        ->exists())->toBeTrue();
})->group('auth');

it('records a logout event', function () {
    $user = User::factory()->create();
    $token = $user->createToken('web')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

    expect(SecurityEvent::query()
        ->where('user_id', $user->id)
        ->where('event', 'logout')
        ->exists())->toBeTrue();
})->group('auth');

it('records a blocked login for a suspended user', function () {
    $user = User::factory()->suspended()->create(['email' => 'blocked@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'blocked@example.com',
        'password' => 'password',
    ])->assertForbidden();

    expect(SecurityEvent::query()
        ->where('user_id', $user->id)
        ->where('event', 'login_blocked')
        ->exists())->toBeTrue();
})->group('auth');

it('security events are append-only and immutable', function () {
    $event = SecurityEvent::query()->create(['event' => 'login_success']);

    expect(fn () => $event->update(['event' => 'changed']))->toThrow(RuntimeException::class);
    expect(fn () => $event->delete())->toThrow(RuntimeException::class);
})->group('auth');
