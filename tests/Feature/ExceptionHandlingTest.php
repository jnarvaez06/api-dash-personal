<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('validation errors on api routes return the standard envelope', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Juan Pérez',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'data' => null,
        ])
        ->assertJsonStructure(['success', 'message', 'data', 'errors']);
});

test('unauthenticated requests to protected api routes return the standard envelope', function () {
    $response = $this->getJson('/api/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
            'data' => null,
        ]);
});

test('requesting a non-existent model on an api route returns the standard envelope', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/accounts/999999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'data' => null,
        ]);
});

test('requesting a non-existent api route returns the standard envelope', function () {
    $response = $this->getJson('/api/no-existe');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'data' => null,
        ]);
});
