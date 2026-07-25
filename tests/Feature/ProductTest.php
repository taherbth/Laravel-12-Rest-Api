<?php
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// This replaces the "class extends TestCase" and "use RefreshDatabase"
// uses(RefreshDatabase::class); // Refresh and clean whole DB

uses(DatabaseTransactions::class); // Refresh and clean only current traction

it('can store a product', function () {
    // 1. Create a user (using the default factory)
    $user = User::factory()->create();

    // 2. Act as that user while making the request
    $response = $this->actingAs($user)
        ->postJson('/api/products', [
            'name'  => 'Test the due',
            'price' => 500,
    ]);

    $response->assertStatus(201)
             ->assertJsonPath('data.product_name', 'Test the due');

    $this->assertDatabaseHas('products', [
        'name' => 'Test the due'
    ]);
});

it('can update an existing product', function () {
    $user = User::factory()->create();
    // Create the product directly in the DB first so we have something to update
    $product = Product::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->putJson("/api/products/{$product->id}", [
            'name'  => 'Updated Name',
            'price' => 150,
        ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.product_name', 'Updated Name');

    $this->assertDatabaseHas('products', [
        'id'   => $product->id,
        'name' => 'Updated Name'
    ]);
});
it('can delete an existing product', function () {
    $user = User::factory()->create();
    // Create the product directly in the DB first so we have something to update
    $product = Product::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->deleteJson("/api/products/{$product->id}");

    $response->assertJsonPath('message', 'Product deleted');
    // Assertion 3: CRITICAL for Delete tests! 
    // Check the database to make sure it's actually gone.
    // Below code is used if don't use soft delete
    // $this->assertDatabaseMissing('products', [
    //     'id' => $product->id
    // ]);
    // Below code is used if  use soft delete
    $this->assertSoftDeleted($product);
});