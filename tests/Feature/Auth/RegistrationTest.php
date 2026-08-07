<?php

use App\Models\User;

it('registers a user successfully', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '5551234567',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'jane@example.com',
                    'status' => 'active',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'status' => 'active',
    ]);
})->group('auth');

it('rejects invalid registration input', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ])->assertUnprocessable();
})->group('auth');

it('rejects duplicate email registration', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Duplicate',
        'email' => 'dup@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])->assertUnprocessable();
})->group('auth');

it('rejects passwords below the minimum length', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Weak',
        'email' => 'weak@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable();
})->group('auth');
