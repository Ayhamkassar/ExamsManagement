<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns the authenticated user profile', function () {
    $user = User::factory()->create(['name' => 'Alice']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me')->assertOk();

    expect($response->json('data.user.name'))->toBe('Alice')
        ->and($response->json('data.user'))->toHaveKeys(['id', 'email', 'status'])
        ->and($response->json('data.roles'))->toBeArray()
        ->and($response->json('data.permissions'))->toBeArray();
})->group('auth');

it('requires authentication for /me', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
})->group('auth');

it('updates the profile safely', function () {
    $user = User::factory()->create(['name' => 'Alice']);
    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/v1/auth/profile', [
        'name' => 'Bob',
        'phone' => '5550001111',
    ])->assertOk();

    expect($response->json('data.user.name'))->toBe('Bob')
        ->and($user->fresh()->phone)->toBe('5550001111');
})->group('auth');

it('does not allow escalating roles, permissions or security fields via profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/auth/profile', [
        'name' => 'Hacker',
        'is_super_admin' => true,
        'status' => 'suspended',
        'email_verified_at' => now()->toISOString(),
    ])->assertOk();

    $fresh = $user->fresh();

    expect($fresh->name)->toBe('Hacker')
        ->and($fresh->is_super_admin)->toBeFalse()
        ->and($fresh->status->value)->toBe('active');
})->group('auth');

it('validates profile update input', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/auth/profile', ['name' => str_repeat('a', 300)])
        ->assertUnprocessable();
})->group('auth');
