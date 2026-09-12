<?php
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

beforeEach(function () {
    RateLimiter::clear('subscription-tier');
});

/**
 * --------------------------------------------------------------------------
 * 1. Rate Limiting Tests (Browser HTTP Interception & Page Loads)
 * --------------------------------------------------------------------------
 */

// it('throttles free tier users after 60 requests on customer pages', function () {
//     $freeUser = User::factory()->create([
//         'subscription_tier' => 'free',
//     ]);

//     $this->browse(function (Browser $browser) use ($freeUser) {
//         $browser->loginAs($freeUser);

//         // Make 60 requests via UI navigation
//         for ($i = 0; $i < 60; $i++) {
//             $browser->visit('/customers')
//                 ->assertPathIs('/customers');
//         }

//         // 61st request should hit throttling response page
//         $browser->visit('/customers')
//             ->assertSee('Too Many Attempts.');
//     });
// });

// it('allows premium users to exceed the free tier rate limit on customer pages', function () {
//     $premiumUser = User::factory()->create([
//         'subscription_tier' => 'premium',
//     ]);

//     $this->browse(function (Browser $browser) use ($premiumUser) {
//         $browser->loginAs($premiumUser);

//         for ($i = 0; $i < 61; $i++) {
//             $browser->visit('/customers')
//                 ->assertPathIs('/customers')
//                 ->assertDontSee('Too Many Attempts.');
//         }
//     });
// });

/**
 * --------------------------------------------------------------------------
 * 2. CRUD Operations (Browser UI Interactions)
 * --------------------------------------------------------------------------
 */

it('can store customer via browser UI', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/customers/create') // Points to your HTML form route, not API
            ->waitFor('input[name="customer_no"]') // Ensures the DOM finishes rendering
            ->type('customer_no', '00525')
            ->type('first_name', 'Test first name')
            ->type('last_name', 'Test last name')
            ->select('gender_id', '1')
            ->type('address', 'test address')
            ->type('city', 'Dhaka')
            ->select('country_id', '23')
            ->press('Save Customer') // Matches button text
            ->waitForText('Test first name Test last name')
            ->assertPathIs('/customers')
            ->assertSee('Test first name Test last name');
    });

    $this->assertDatabaseHas('customers', [
        'first_name' => 'Test first name',
    ]);
});

// it('can update an existing customer via browser UI', function () {
//     $user = User::factory()->create();
//     $customer = Customer::factory()->create([
//         'customer_no' => '00525',
//         'first_name'  => 'Test first name',
//         'last_name'   => 'Test last name',
//         'gender_id'   => 1,
//         'address'     => 'test address',
//         'city'        => 'Dhaka',
//         'country_id'  => 23,
//     ]);

//     $this->browse(function (Browser $browser) use ($user, $customer) {
//         $browser->loginAs($user)
//             ->visit("/customers/{$customer->id}/edit")
//             ->clear('first_name')
//             ->type('first_name', 'Test first name Updated')
//             ->clear('last_name')
//             ->type('last_name', 'Test last name Updated')
//             ->clear('address')
//             ->type('address', 'test address Updated')
//             ->press('Update Customer')
//             ->waitForText('Test first name Updated Test last name Updated')
//             ->assertSee('Test first name Updated Test last name Updated');
//     });

//     $this->assertDatabaseHas('customers', [
//         'id'         => $customer->id,
//         'first_name' => 'Test first name Updated',
//     ]);
// });

// it('can delete selected customers via browser UI table', function () {
//     $user = User::factory()->create();
//     $customerA = Customer::factory()->create([
//         'customer_no' => '00525',
//         'first_name'  => 'Test first name A',
//         'last_name'   => 'Test last name A',
//     ]);
//     $customerB = Customer::factory()->create([
//         'customer_no' => '00526',
//         'first_name'  => 'Test first name B',
//         'last_name'   => 'Test last name B',
//     ]);

//     $this->browse(function (Browser $browser) use ($user, $customerA, $customerB) {
//         $browser->loginAs($user)
//             ->visit('/customers')
//             // Check rows corresponding to customer IDs
//             ->check("input[value='{$customerA->id}']")
//             ->check("input[value='{$customerB->id}']")
//             ->press('Delete Selected') // Bulk delete action trigger
//             ->whenAvailable('.modal', function ($modal) {
//                 $modal->press('Confirm Delete');
//             })
//             ->waitForText('Customer(s) deleted successfully.')
//             ->assertSee('Customer(s) deleted successfully.');
//     });

//     $this->assertSoftDeleted($customerA);
//     $this->assertSoftDeleted($customerB);
// });

// it('shows validation error when attempting to delete without selecting items', function () {
//     $user = User::factory()->create();

//     $this->browse(function (Browser $browser) use ($user) {
//         $browser->loginAs($user)
//             ->visit('/customers')
//             // Attempt to submit delete without checking any checkboxes
//             ->press('Delete Selected')
//             ->waitForText('No items selected for deletion.')
//             ->assertSee('No items selected for deletion.');
//     });
// });
