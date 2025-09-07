<?php

use Pest\Laravel\Pest;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the amount of code duplication.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toHaveValidationError', function (string $field) {
    $this->toBeInstanceOf(\Illuminate\Testing\TestResponse::class);
    
    return $this->and($this->value->assertSessionHasErrors($field));
});

expect()->extend('toBeValidJson', function () {
    json_decode($this->value);
    
    return $this->and(json_last_error())->toBe(JSON_ERROR_NONE);
});

expect()->extend('toHaveFlashMessage', function (string $key, ?string $message = null) {
    $this->toBeInstanceOf(\Illuminate\Testing\TestResponse::class);
    
    if ($message) {
        return $this->and($this->value->assertSessionHas($key, $message));
    }
    
    return $this->and($this->value->assertSessionHas($key));
});

/*
|--------------------------------------------------------------------------
| Test Groups
|--------------------------------------------------------------------------
*/

// Define test groups for better organization
pest()->group('auth', 'authentication');
pest()->group('api', 'rest-api');
pest()->group('admin', 'admin-panel');
pest()->group('gdpr', 'privacy', 'compliance');
pest()->group('cart', 'shopping');
pest()->group('checkout', 'orders');
pest()->group('products', 'catalog');
pest()->group('slow', 'integration');
pest()->group('unit', 'fast');
pest()->group('feature', 'integration');

/*
|--------------------------------------------------------------------------
| Shared Test Data
|--------------------------------------------------------------------------
*/

function createTestUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create($attributes);
}

function createTestProduct(array $attributes = []): \App\Models\Product
{
    $category = \App\Models\Category::factory()->create();
    
    return \App\Models\Product::factory()->create(array_merge([
        'category_id' => $category->id,
    ], $attributes));
}

function createTestOrder(?\App\Models\User $user = null, array $attributes = []): \App\Models\Order
{
    $user = $user ?: createTestUser();
    
    return \App\Models\Order::factory()->create(array_merge([
        'user_id' => $user->id,
    ], $attributes));
}

/*
|--------------------------------------------------------------------------
| Authentication Helpers
|--------------------------------------------------------------------------
*/

function actingAsAdmin(): \App\Models\User
{
    $admin = createTestUser();
    $admin->assignRole('admin');
    test()->actingAs($admin);
    
    return $admin;
}

function actingAsSeller(): \App\Models\User
{
    $seller = createTestUser();
    $seller->assignRole('seller');
    test()->actingAs($seller);
    
    return $seller;
}

function actingAsBuyer(): \App\Models\User
{
    $buyer = createTestUser();
    $buyer->assignRole('buyer');
    test()->actingAs($buyer);
    
    return $buyer;
}

/*
|--------------------------------------------------------------------------
| API Testing Helpers
|--------------------------------------------------------------------------
*/

function apiRequest(string $method, string $uri, array $data = [], ?\App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    if ($user) {
        $token = $user->createToken('test-token')->plainTextToken;
        return test()->withHeader('Authorization', "Bearer {$token}")
                   ->json($method, $uri, $data);
    }
    
    return test()->json($method, $uri, $data);
}

function apiGet(string $uri, ?\App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return apiRequest('GET', $uri, [], $user);
}

function apiPost(string $uri, array $data = [], ?\App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return apiRequest('POST', $uri, $data, $user);
}

function apiPut(string $uri, array $data = [], ?\App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return apiRequest('PUT', $uri, $data, $user);
}

function apiDelete(string $uri, ?\App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return apiRequest('DELETE', $uri, [], $user);
}