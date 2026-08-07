<?php

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

it('verifies an email via a signed url', function () {
    $user = User::factory()->unverified()->create(['email' => 'verify@example.com']);

    $url = URL::temporarySignedRoute('api.v1.auth.email.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->postJson($url)->assertOk()->assertJson(['success' => true]);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
})->group('auth');

it('rejects an invalid verification signature', function () {
    $user = User::factory()->unverified()->create(['email' => 'bad@example.com']);

    $this->postJson('/api/v1/auth/email/verify', [
        'id' => $user->id,
        'hash' => sha1($user->email),
        'signature' => 'invalid-signature',
        'expires' => now()->addMinutes(60)->timestamp,
    ])->assertStatus(400);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->group('auth');

it('rejects a mismatched verification hash', function () {
    $user = User::factory()->unverified()->create(['email' => 'hash@example.com']);

    $url = URL::temporarySignedRoute('api.v1.auth.email.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1('someone-else@example.com'),
    ]);

    $this->postJson($url)->assertStatus(400);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->group('auth');

it('resends a verification email to an authenticated unverified user', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/email/resend')->assertOk();

    Notification::assertSentTo($user, VerifyEmail::class);
})->group('auth');

it('does not resend for an already verified email', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/email/resend')->assertStatus(400);
})->group('auth');

it('requires authentication to resend verification', function () {
    $this->postJson('/api/v1/auth/email/resend')->assertUnauthorized();
})->group('auth');
