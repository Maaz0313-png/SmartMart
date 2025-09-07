<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_empty_cart(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->get('/cart');

        $response->assertStatus(200)
            ->assertSee('Your cart is empty');
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        // Add product first time
        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // Add same product again
        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5
        ]);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = $this->actingAsBuyer();
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

        $response = $this->patch("/cart/{$cartItem->id}", [
            'quantity' => 5
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->delete("/cart/{$cartItem->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('carts', [
            'id' => $cartItem->id
        ]);
    }

    public function test_user_can_clear_entire_cart(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['category_id' => $category->id]);

        foreach ($products as $product) {
            Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        }

        $response = $this->delete('/cart');

        $response->assertRedirect();

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id
        ]);
    }

    public function test_cart_count_endpoint_returns_correct_count(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $products = Product::factory()->count(2)->create(['category_id' => $category->id]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $products[0]->id,
            'quantity' => 3
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $products[1]->id,
            'quantity' => 2
        ]);

        $response = $this->get('/cart/count');

        $response->assertStatus(200)
            ->assertJson(['count' => 5]);
    }

    public function test_user_can_apply_valid_coupon(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00
        ]);

        $coupon = Coupon::factory()->create([
            'code' => 'SAVE10',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => now()->addDays(30)
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->post('/cart/coupon', [
            'coupon_code' => 'SAVE10'
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_cannot_apply_expired_coupon(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $coupon = Coupon::factory()->create([
            'code' => 'EXPIRED',
            'discount_percentage' => 10,
            'is_active' => true,
            'expires_at' => now()->subDays(1)
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->post('/cart/coupon', [
            'coupon_code' => 'EXPIRED'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['coupon_code']);
    }

    public function test_cannot_exceed_stock_quantity_when_updating_cart(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 3
        ]);

        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response = $this->patch("/cart/{$cartItem->id}", [
            'quantity' => 5
        ]);

        $response->assertSessionHasErrors(['quantity']);

        $this->assertDatabaseHas('carts', [
            'id' => $cartItem->id,
            'quantity' => 1
        ]);
    }

    public function test_cart_calculates_total_correctly(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 30.00
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $response = $this->get('/cart');

        $response->assertStatus(200)
            ->assertSee('$130.00'); // 2 * 50 + 1 * 30
    }

    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    public function test_users_cannot_access_other_users_cart_items(): void
    {
        $user1 = $this->createBuyer();
        $user2 = $this->createBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $cartItem = Cart::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $this->actingAs($user2);

        $response = $this->patch("/cart/{$cartItem->id}", [
            'quantity' => 5
        ]);

        $response->assertStatus(404);
    }
}