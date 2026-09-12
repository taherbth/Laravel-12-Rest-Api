<?php
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class); // Refresh and clean only current traction

beforeEach(function () {
    // Reset the rate limiter state before each test to avoid false positives
    RateLimiter::clear('subscription-tier');
});

it('throttles free tier users after 60 requests on customer routes', function () {
    $freeUser = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Make 60 allowed requests
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($freeUser)
             ->getJson('/api/v1/customers')
             ->assertOk();
    }

    // 61st request must trigger 429 Too Many Requests
    $this->actingAs($freeUser)
         ->getJson('/api/v1/customers')
         ->assertStatus(429)
         ->assertJsonPath('message', 'Too Many Attempts.');
});

it('allows premium users to exceed the free tier rate limit on customer routes', function () {
    $premiumUser = User::factory()->create([
        'subscription_tier' => 'premium',
    ]);

    // Premium user makes 61 requests without getting blocked at 60
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($premiumUser)
             ->getJson('/api/v1/customers')
             ->assertOk();
    }
});

it('resets the rate limit after the decay time passes on customer routes', function () {
    $user = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Exhaust allowed attempts
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($user)->getJson('/api/v1/customers');
    }

    // Confirm blocked
    $this->actingAs($user)->getJson('/api/v1/customers')->assertStatus(429);

    // Fast-forward time by 61 seconds
    $this->travel(61)->seconds();

    // User can make requests again
    $this->actingAs($user)
         ->getJson('/api/v1/customers')
         ->assertOk();
});

it('returns rate limit headers in the response on customer routes', function () {
    $user = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/customers');
    
    $response->assertHeader('X-RateLimit-Limit', '60')
             ->assertHeader('X-RateLimit-Remaining', '59');
});

it('can store customer', function () {
    // 1. Create a user (using the default factory)
    $user = User::factory()->create();

    // 2. Act as that user while making the request
    $response = $this->actingAs($user)
        ->postJson('/api/v1/customers', [
            'customer_no' => '00525',
            'first_name'  => 'Test first name',
            'last_name' => 'Test last name',
            'gender_id' => 1,
            'address' => 'test address',
            'city' => 'Dhaka',
            'country_id' => 23

    ]);

    $response->assertStatus(201)
             ->assertJsonPath('data.full_name', 'Test first name Test last name');

    $this->assertDatabaseHas('customers', [
        'first_name' => 'Test first name'
    ]);
});

it('can update an existing customer', function () {
    $user = User::factory()->create();
    // Create the customer directly in the DB first so we have something to update
    $customer = Customer::factory()->create(
        [
            'customer_no' => '00525',
            'first_name'  => 'Test first name',
            'last_name' => 'Test last name',
            'gender_id' => 1,
            'address' => 'test address',
            'city' => 'Dhaka',
            'country_id' => 23

        ]);

    $response = $this->actingAs($user)
        ->putJson("/api/v1/customers/{$customer->id}", 
            [
                'customer_no' => '00525',
                'first_name'  => 'Test first name Updated',
                'last_name' => 'Test last name Updated',
                'gender_id' => 1,
                'address' => 'test address Updated',
                'city' => 'Dhaka',
                'country_id' => 23
        ]);

    $response->assertStatus(200)
             ->assertJsonPath('data.full_name', 'Test first name Updated Test last name Updated');

    $this->assertDatabaseHas('customers', [
        'id'   => $customer->id,
        'first_name' => 'Test first name Updated'
    ]);
});

it('can delete an existing customer', function () {
    $user = User::factory()->create();
    // Create the customer directly in the DB first so we have something to update
    $customerA = Customer::factory()->create(
        [
            'customer_no' => '00525',
            'first_name'  => 'Test first name',
            'last_name' => 'Test last name',
            'gender_id' => 1,
            'address' => 'test address',
            'city' => 'Dhaka',
            'country_id' => 23

        ]);

    $customerB = Customer::factory()->create();    

    $response = $this->actingAs($user)
        ->postJson("/api/v1/customers/remove_customer", 
        [
            'item_ids' => [$customerA->id, $customerB->id],
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Customer(s) deleted successfully.',
        ]);

    // Verify soft deletion or database state
    $this->assertSoftDeleted($customerA);
    $this->assertSoftDeleted($customerB);

    // Note: If your application uses hard deletes instead of soft deletes, replace $this->assertSoftDeleted($customerA) with $this->assertDatabaseMissing('customers', ['id' => $customerA->id]).
});

it('can delete : returns a 400 error when item_ids is empty or not an array', function (mixed $invalidItemIds) {
    $user = User::factory()->create();

    $customerA = Customer::factory()->create(
        [
            'customer_no' => '00525',
            'first_name'  => 'Test first name',
            'last_name' => 'Test last name',
            'gender_id' => 1,
            'address' => 'test address',
            'city' => 'Dhaka',
            'country_id' => 23

        ]);

    $response = $this->actingAs($user)
        ->postJson("/api/v1/customers/remove_customer", 
        [
            'item_ids' => $invalidItemIds,
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'No items selected for deletion.',
        ]);
})->with([
    'empty array' => [[]],
    'null value'  => [null],
    'string'      => ['invalid-id'],
    'integer'     => [123],
]);

 