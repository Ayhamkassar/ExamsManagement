<?php

use App\Support\Api\ApiResponse;
use Illuminate\Support\Facades\Route;

it('returns consistent success response structure', function () {
    Route::get('/api/v1/test-success', fn () => ApiResponse::success(['key' => 'value']));

    $response = $this->getJson('/api/v1/test-success');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Operation completed successfully.',
            'data' => ['key' => 'value'],
        ]);
});

it('returns consistent validation error structure', function () {
    Route::post('/api/v1/test-validation', function () {
        request()->validate(['email' => 'required|email']);
    });

    $response = $this->postJson('/api/v1/test-validation', []);

    $response->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ])
        ->assertJsonStructure(['errors' => ['email']]);
});

it('does not expose stack traces in production mode', function () {
    config(['app.debug' => false]);

    Route::get('/api/v1/test-error', function () {
        throw new RuntimeException('Sensitive internal detail');
    });

    $response = $this->getJson('/api/v1/test-error');

    $response->assertStatus(500)
        ->assertJson([
            'success' => false,
            'message' => 'An unexpected error occurred.',
        ]);
});
