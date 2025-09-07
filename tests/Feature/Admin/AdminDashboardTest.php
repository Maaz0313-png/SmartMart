<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->get('/admin');

        $response->assertStatus(200)
            ->assertSee('Admin Dashboard');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $buyer = $this->actingAsBuyer();

        $response = $this->get('/admin');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_shows_key_metrics(): void
    {
        $admin = $this->actingAsAdmin();
        
        // Create test data
        $users = User::factory()->count(10)->create();
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create(['category_id' => $category->id]);
        $orders = Order::factory()->count(3)->create(['status' => 'completed']);

        $response = $this->get('/admin');

        $response->assertStatus(200)
            ->assertSee('Total Users')
            ->assertSee('Total Products')
            ->assertSee('Total Orders');
    }

    public function test_admin_can_view_users_list(): void
    {
        $admin = $this->actingAsAdmin();
        $users = User::factory()->count(5)->create();

        $response = $this->get('/admin/users');

        $response->assertStatus(200);
        
        foreach ($users as $user) {
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'buyer'
        ]);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com'
        ]);
    }

    public function test_admin_can_edit_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->patch("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect("/admin/users/{$user->id}");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');

        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }

    public function test_admin_can_impersonate_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->post("/admin/users/{$user->id}/impersonate");

        $response->assertRedirect('/dashboard');

        // Check if session has impersonation data
        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_admin_can_stop_impersonating(): void
    {
        $admin = $this->actingAsAdmin();
        $user = User::factory()->create();

        // Start impersonation
        session(['impersonate' => $admin->id]);
        $this->actingAs($user);

        $response = $this->post('/admin/users/stop-impersonating');

        $response->assertRedirect('/admin/users');
        $this->assertFalse(session()->has('impersonate'));
    }

    public function test_admin_can_perform_bulk_user_actions(): void
    {
        $admin = $this->actingAsAdmin();
        $users = User::factory()->count(3)->create();

        $response = $this->post('/admin/users/bulk-action', [
            'action' => 'activate',
            'user_ids' => $users->pluck('id')->toArray()
        ]);

        $response->assertRedirect('/admin/users');

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'is_active' => true
            ]);
        }
    }

    public function test_admin_can_view_user_activity(): void
    {
        $admin = $this->actingAsAdmin();
        $user = User::factory()->create();

        $response = $this->get("/admin/users/{$user->id}/activity");

        $response->assertStatus(200)
            ->assertSee('User Activity');
    }

    public function test_admin_can_manage_products(): void
    {
        $admin = $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->post('/admin/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => $category->id,
            'stock_quantity' => 100,
            'is_active' => true
        ]);

        $response->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99
        ]);
    }

    public function test_admin_can_update_product_inventory(): void
    {
        $admin = $this->actingAsAdmin();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 50
        ]);

        $response = $this->patch("/admin/products/{$product->id}/inventory", [
            'stock_quantity' => 100,
            'low_stock_threshold' => 10
        ]);

        $response->assertRedirect("/admin/products/{$product->id}");

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 100
        ]);
    }

    public function test_admin_can_view_orders(): void
    {
        $admin = $this->actingAsAdmin();
        $orders = Order::factory()->count(5)->create();

        $response = $this->get('/admin/orders');

        $response->assertStatus(200);

        foreach ($orders as $order) {
            $response->assertSee($order->order_number);
        }
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = $this->actingAsAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patch("/admin/orders/{$order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertRedirect("/admin/orders/{$order->id}");

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing'
        ]);
    }

    public function test_admin_can_mark_order_as_shipped(): void
    {
        $admin = $this->actingAsAdmin();
        $order = Order::factory()->create(['status' => 'processing']);

        $response = $this->post("/admin/orders/{$order->id}/ship", [
            'tracking_number' => 'TRACK123456',
            'shipping_carrier' => 'UPS'
        ]);

        $response->assertRedirect("/admin/orders/{$order->id}");

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'tracking_number' => 'TRACK123456'
        ]);
    }

    public function test_admin_can_perform_bulk_order_updates(): void
    {
        $admin = $this->actingAsAdmin();
        $orders = Order::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->post('/admin/orders/bulk-update', [
            'action' => 'mark_processing',
            'order_ids' => $orders->pluck('id')->toArray()
        ]);

        $response->assertRedirect('/admin/orders');

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'processing'
            ]);
        }
    }

    public function test_admin_can_view_analytics(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->get('/admin/analytics');

        $response->assertStatus(200)
            ->assertSee('Analytics Overview');
    }

    public function test_admin_can_access_settings(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->get('/admin/settings');

        $response->assertStatus(200)
            ->assertSee('Application Settings');
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->patch('/admin/settings', [
            'site_name' => 'SmartMart Updated',
            'site_description' => 'Updated description',
            'contact_email' => 'admin@smartmart.com'
        ]);

        $response->assertRedirect('/admin/settings');
    }

    public function test_admin_can_clear_cache(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->post('/admin/settings/clear-cache');

        $response->assertRedirect('/admin/settings')
            ->assertSessionHas('success');
    }

    public function test_seller_cannot_access_admin_functions(): void
    {
        $seller = $this->actingAsSeller();

        $response = $this->get('/admin/users');

        $response->assertStatus(403);
    }
}