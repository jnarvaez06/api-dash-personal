<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index returns only the authenticated user categories', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Category::factory()->count(2)->for($user)->create();
    Category::factory()->count(3)->for($otherUser)->create();

    $response = $this->actingAs($user)->getJson('/api/categories');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonCount(2, 'data.data');
});

test('store creates a category for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/categories', [
        'name' => 'Alimentación',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => ['name' => 'Alimentación', 'is_active' => true],
        ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Alimentación',
    ]);
});

test('store fails validation without a name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/categories', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false, 'data' => null])
        ->assertJsonStructure(['success', 'message', 'data', 'errors']);
});

test('show returns a single category owned by the authenticated user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->getJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => ['id' => $category->id, 'name' => $category->name],
        ]);
});

test('show returns 404 for a category belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->getJson("/api/categories/{$category->id}");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'data' => null,
        ]);
});

test('update modifies a category owned by the authenticated user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['name' => 'Original']);

    $response = $this->actingAs($user)->patchJson("/api/categories/{$category->id}", [
        'name' => 'Actualizada',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'data' => ['name' => 'Actualizada']]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Actualizada',
    ]);
});

test('update returns 404 for a category belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->patchJson("/api/categories/{$category->id}", [
        'name' => 'Hackeo',
    ]);

    $response->assertStatus(404);
});

test('destroy soft-disables the category instead of deleting it', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully.',
            'data' => null,
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

test('destroy returns 404 for a category belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(404);
});
