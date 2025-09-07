<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_displays_active_products(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        
        // Create active and inactive products
        $activeProducts = Product::factory()->count(3)->create([
            'is_active' => true,
            'category_id' => $category->id
        ]);
        $inactiveProduct = Product::factory()->create([
            'is_active' => false,
            'category_id' => $category->id
        ]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        
        foreach ($activeProducts as $product) {
            $response->assertSee($product->name);
        }
        
        $response->assertDontSee($inactiveProduct->name);
    }

    public function test_product_show_displays_product_details(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->get("/products/{$product->id}");

        $response->assertStatus(200)
            ->assertSee($product->name)
            ->assertSee($product->description)
            ->assertSee('$' . number_format($product->price, 2));
    }

    public function test_inactive_product_returns_404(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'is_active' => false,
            'category_id' => $category->id
        ]);

        $response = $this->get("/products/{$product->id}");

        $response->assertStatus(404);
    }

    public function test_product_search_functionality(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        
        $product1 = Product::factory()->create([
            'name' => 'Gaming Laptop',
            'category_id' => $category->id
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Office Chair',
            'category_id' => $category->id
        ]);

        $response = $this->get('/search?q=laptop');

        $response->assertStatus(200)
            ->assertSee($product1->name)
            ->assertDontSee($product2->name);
    }

    public function test_add_product_to_cart(): void
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

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 0
        ]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertSessionHasErrors(['quantity']);
        
        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5
        ]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_product_filtering_by_category(): void
    {
        $user = $this->actingAsBuyer();
        $category1 = Category::factory()->create(['name' => 'Electronics']);
        $category2 = Category::factory()->create(['name' => 'Clothing']);
        
        $electronicsProduct = Product::factory()->create([
            'category_id' => $category1->id,
            'name' => 'Smartphone'
        ]);
        $clothingProduct = Product::factory()->create([
            'category_id' => $category2->id,
            'name' => 'T-Shirt'
        ]);

        $response = $this->get("/products?category={$category1->id}");

        $response->assertStatus(200)
            ->assertSee($electronicsProduct->name)
            ->assertDontSee($clothingProduct->name);
    }

    public function test_product_price_filtering(): void
    {
        $user = $this->actingAsBuyer();
        $category = Category::factory()->create();
        
        $cheapProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00
        ]);
        $expensiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 200.00
        ]);

        $response = $this->get('/products?min_price=100&max_price=300');

        $response->assertStatus(200)
            ->assertSee($expensiveProduct->name)
            ->assertDontSee($cheapProduct->name);
    }
}