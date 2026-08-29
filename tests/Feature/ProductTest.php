<?php
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// This replaces the "class extends TestCase" and "use RefreshDatabase"
// uses(RefreshDatabase::class); // Refresh and clean whole DB

uses(DatabaseTransactions::class); // Refresh and clean only current traction

beforeEach(function () {
    // Reset the rate limiter state before each test to avoid false positives
    RateLimiter::clear('subscription-tier');
});

it('throttles free tier users after 60 requests on product routes', function () {
    $freeUser = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Make 60 allowed requests
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($freeUser)
             ->getJson('/api/v1/products')
             ->assertOk();
    }

    // 61st request must trigger 429 Too Many Requests
    $this->actingAs($freeUser)
         ->getJson('/api/v1/products')
         ->assertStatus(429)
         ->assertJsonPath('message', 'Too Many Attempts.');
});

it('allows premium users to exceed the free tier rate limit', function () {
    $premiumUser = User::factory()->create([
        'subscription_tier' => 'premium',
    ]);

    // Premium user makes 61 requests without getting blocked at 60
    for ($i = 0; $i < 61; $i++) {
        $this->actingAs($premiumUser)
             ->getJson('/api/v1/products')
             ->assertOk();
    }
});

it('resets the rate limit after the decay time passes', function () {
    $user = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Exhaust allowed attempts
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($user)->getJson('/api/v1/products');
    }

    // Confirm blocked
    $this->actingAs($user)->getJson('/api/v1/products')->assertStatus(429);

    // Fast-forward time by 61 seconds
    $this->travel(61)->seconds();

    // User can make requests again
    $this->actingAs($user)
         ->getJson('/api/v1/products')
         ->assertOk();
});

it('returns rate limit headers in the response', function () {
    $user = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/products');
    
    $response->assertHeader('X-RateLimit-Limit', '60')
             ->assertHeader('X-RateLimit-Remaining', '59');
});

it('can store a product', function () {
    // 1. Create a user (using the default factory)
    $user = User::factory()->create();

    // 2. Act as that user while making the request
    $response = $this->actingAs($user)
        ->postJson('/api/v1/products', [
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
        ->putJson("/api/v1/products/{$product->id}", [
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
        ->deleteJson("/api/v1/products/{$product->id}");

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