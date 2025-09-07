<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_checkout_with_items_in_cart(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user);

        $response = $this->get('/checkout');

        $response->assertStatus(200)
            ->assertSee('Checkout');
    }

    public function test_user_cannot_access_checkout_with_empty_cart(): void
    {
        $user = $this->actingAsBuyer();

        $response = $this->get('/checkout');

        $response->assertRedirect('/cart')
            ->assertSessionHas('error');
    }

    public function test_user_can_complete_checkout_process(): void
    {
        $user = $this->actingAsBuyer();
        $product = $this->addProductToCart($user);
        
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);

        // Cart should be cleared after checkout
        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id
        ]);
    }

    public function test_checkout_validation_requires_billing_address(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user);

        $response = $this->post('/checkout', [
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
        ]);

        $response->assertSessionHasErrors(['billing_address_id']);
    }

    public function test_checkout_validation_requires_payment_method(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user);
        
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_checkout_applies_coupon_discount(): void
    {
        $user = $this->actingAsBuyer();
        $product = $this->addProductToCart($user, 100.00);
        
        $coupon = Coupon::factory()->create([
            'code' => 'SAVE20',
            'discount_percentage' => 20,
            'is_active' => true,
            'expires_at' => now()->addDays(30)
        ]);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        // Apply coupon in cart first
        $this->post('/cart/coupon', ['coupon_code' => 'SAVE20']);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(80.00, $order->total_amount); // 100 - 20% discount
    }

    public function test_checkout_calculates_shipping_cost(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user, 50.00);
        
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
            'shipping_method' => 'standard',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertGreaterThan(50.00, $order->total_amount); // Should include shipping
    }

    public function test_checkout_fails_with_insufficient_stock(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 1
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5 // More than available stock
        ]);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id
        ]);
    }

    public function test_checkout_confirmation_page_displays_order_details(): void
    {
        $user = $this->actingAsBuyer();
        $product = $this->addProductToCart($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'total_amount' => 100.00
        ]);

        $response = $this->get("/checkout/confirmation/{$order->id}");

        $response->assertStatus(200)
            ->assertSee($order->order_number)
            ->assertSee('$100.00');
    }

    public function test_user_cannot_access_other_users_checkout_confirmation(): void
    {
        $user1 = $this->createBuyer();
        $user2 = $this->createBuyer();
        
        $order = Order::factory()->create([
            'user_id' => $user1->id,
            'status' => 'confirmed'
        ]);

        $this->actingAs($user2);

        $response = $this->get("/checkout/confirmation/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/login');
    }

    public function test_checkout_with_different_billing_and_shipping_addresses(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user);
        
        $billingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $shippingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'shipping'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $billingAddress->id,
            'shipping_address_id' => $shippingAddress->id,
            'payment_method' => 'stripe',
            'payment_token' => 'pm_card_visa',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals($billingAddress->id, $order->billing_address_id);
        $this->assertEquals($shippingAddress->id, $order->shipping_address_id);
    }

    public function test_checkout_with_paypal_payment_method(): void
    {
        $user = $this->actingAsBuyer();
        $this->addProductToCart($user);
        
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'type' => 'billing'
        ]);

        $response = $this->post('/checkout', [
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'payment_method' => 'paypal',
            'paypal_payment_id' => 'PAYID-123456',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals('paypal', $order->payment_method);
    }

    private function addProductToCart(User $user, float $price = 100.00): Product
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => $price,
            'stock_quantity' => 10
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        return $product;
    }
}