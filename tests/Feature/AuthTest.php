<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('register creates the default categories for the new user', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true]);

    $user = User::where('email', 'juan@example.com')->first();

    expect($user->categories()->pluck('name')->sort()->values()->all())
        ->toEqual(collect(Category::DEFAULT_NAMES)->sort()->values()->all());

    expect($user->categories()->where('is_active', true)->count())
        ->toBe(count(Category::DEFAULT_NAMES));
});
