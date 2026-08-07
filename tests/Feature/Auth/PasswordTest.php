<?php

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;

it('forgot password returns a generic response and sends a notification', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'forgot@example.com']);

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'forgot@example.com',
    ])->assertOk();

    expect($response->json('message'))->toContain('reset link');
    Notification::assertSentTo($user, ResetPassword::class);
})->group('auth');

it('forgot password does not reveal whether the email exists', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'does-not-exist@example.com',
    ])->assertOk();

    expect($response->json('message'))->toContain('reset link');
})->group('auth');

it('resets the password with a valid token and revokes sessions', function () {
    $user = User::factory()->create(['email' => 'reset@example.com']);
    $user->createToken('old-device')->plainTextToken;

    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset@example.com',
        'token' => $token,
        'password' => 'newpass12345',
        'password_confirmation' => 'newpass12345',
    ])->assertOk();

    expect(Hash::check('newpass12345', $user->fresh()->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
})->group('auth');

it('rejects an invalid reset token', function () {
    User::factory()->create(['email' => 'invalid-token@example.com']);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'invalid-token@example.com',
        'token' => 'not-a-real-token',
        'password' => 'newpass12345',
        'password_confirmation' => 'newpass12345',
    ])->assertStatus(422);
})->group('auth');

it('changes the password after verifying the current password', function () {
    $user = User::factory()->create();
    $token = $user->createToken('current')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/change-password', [
        'current_password' => 'password',
        'password' => 'newpass12345',
        'password_confirmation' => 'newpass12345',
    ])->assertOk();

    expect(Hash::check('newpass12345', $user->fresh()->password))->toBeTrue();
})->group('auth');

it('rejects a change password with the wrong current password', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/change-password', [
        'current_password' => 'wrong-current',
        'password' => 'newpass12345',
        'password_confirmation' => 'newpass12345',
    ])->assertStatus(422);
})->group('auth');
