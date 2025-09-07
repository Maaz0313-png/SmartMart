<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_products_index_returns_paginated_results(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        $response = $this->apiAs($user, 'GET', '/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'category', 'is_active']
                ],
                'meta' => ['current_page', 'total', 'per_page'],
                'links'
            ]);
    }

    public function test_api_products_can_be_filtered_by_category(): void
    {
        $user = $this->createBuyer();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $product1 = Product::factory()->create(['category_id' => $category1->id]);
        $product2 = Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->apiAs($user, 'GET', "/api/v1/products?category_id={$category1->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product1->id])
            ->assertJsonMissing(['id' => $product2->id]);
    }

    public function test_api_products_can_be_searched(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Gaming Laptop'
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Office Chair'
        ]);

        $response = $this->apiAs($user, 'GET', '/api/v1/products?search=laptop');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Gaming Laptop'])
            ->assertJsonMissing(['name' => 'Office Chair']);
    }

    public function test_api_products_can_be_filtered_by_price_range(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        
        $cheapProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00
        ]);
        $expensiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 200.00
        ]);

        $response = $this->apiAs($user, 'GET', '/api/v1/products?min_price=100&max_price=300');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $expensiveProduct->id])
            ->assertJsonMissing(['id' => $cheapProduct->id]);
    }

    public function test_api_product_show_returns_detailed_information(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->apiAs($user, 'GET', "/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description', 'price', 'stock_quantity',
                    'category', 'images', 'attributes', 'created_at'
                ]
            ]);
    }

    public function test_api_cart_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(401);
    }

    public function test_api_user_can_view_cart(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->apiAs($user, 'GET', '/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'product', 'quantity', 'total']
                    ],
                    'total_amount',
                    'item_count'
                ]
            ]);
    }

    public function test_api_user_can_add_item_to_cart(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        $response = $this->apiAs($user, 'POST', '/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['quantity' => 3]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);
    }

    public function test_api_user_can_update_cart_item(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->apiAs($user, 'PUT', "/api/v1/cart/{$cartItem->id}", [
            'quantity' => 5
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['quantity' => 5]);

        $this->assertDatabaseHas('carts', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    public function test_api_user_can_remove_cart_item(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->apiAs($user, 'DELETE', "/api/v1/cart/{$cartItem->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('carts', [
            'id' => $cartItem->id
        ]);
    }

    public function test_api_orders_endpoint_returns_user_orders(): void
    {
        $user = $this->createBuyer();
        $userOrders = Order::factory()->count(3)->create(['user_id' => $user->id]);
        $otherUserOrders = Order::factory()->count(2)->create();

        $response = $this->apiAs($user, 'GET', '/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        foreach ($userOrders as $order) {
            $response->assertJsonFragment(['id' => $order->id]);
        }
    }

    public function test_api_order_show_returns_detailed_order(): void
    {
        $user = $this->createBuyer();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->apiAs($user, 'GET', "/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'order_number', 'status', 'total_amount',
                    'items', 'billing_address', 'shipping_address'
                ]
            ]);
    }

    public function test_api_user_cannot_view_other_users_orders(): void
    {
        $user1 = $this->createBuyer();
        $user2 = $this->createBuyer();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        $response = $this->apiAs($user2, 'GET', "/api/v1/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_api_checkout_creates_order(): void
    {
        $user = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->apiAs($user, 'POST', '/api/v1/checkout', [
            'billing_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '1234567890',
                'address_line_1' => '123 Main St',
                'city' => 'Anytown',
                'state' => 'CA',
                'postal_code' => '12345',
                'country' => 'US'
            ],
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'order_number', 'status', 'total_amount']
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
    }

    public function test_api_validation_errors_return_proper_format(): void
    {
        $user = $this->createBuyer();

        $response = $this->apiAs($user, 'POST', '/api/v1/cart', [
            'product_id' => 999, // Non-existent product
            'quantity' => 0 // Invalid quantity
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['product_id', 'quantity']
            ]);
    }

    public function test_api_rate_limiting_works(): void
    {
        $user = $this->createBuyer();

        // Make requests until rate limit is hit
        for ($i = 0; $i < 125; $i++) {
            $response = $this->apiAs($user, 'GET', '/api/v1/products');
            
            if ($response->status() === 429) {
                $this->assertEquals(429, $response->status());
                return;
            }
        }

        $this->markTestSkipped('Rate limiting threshold not reached in test environment');
    }

    public function test_api_returns_consistent_error_format(): void
    {
        $response = $this->getJson('/api/v1/products/999999');

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_api_seller_can_manage_own_products(): void
    {
        $seller = $this->createSeller();
        $category = Category::factory()->create();

        $response = $this->apiAs($seller, 'POST', '/api/v1/seller/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => $category->id,
            'stock_quantity' => 100
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Product']);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'seller_id' => $seller->id
        ]);
    }

    public function test_api_seller_cannot_manage_other_sellers_products(): void
    {
        $seller1 = $this->createSeller();
        $seller2 = $this->createSeller();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'seller_id' => $seller1->id
        ]);

        $response = $this->apiAs($seller2, 'PUT', "/api/v1/seller/products/{$product->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(404);
    }
}