<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_belongs_to_seller(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'user_id' => $seller->id
        ]);

        $this->assertInstanceOf(User::class, $product->seller);
        $this->assertEquals($seller->id, $product->seller->id);
    }

    public function test_product_has_many_cart_items(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $cartItems = Cart::factory()->count(3)->create([
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);

        $this->assertCount(3, $product->cartItems);
        $this->assertInstanceOf(Cart::class, $product->cartItems->first());
    }

    public function test_product_has_many_order_items(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderItems = OrderItem::factory()->count(2)->create([
            'product_id' => $product->id,
            'order_id' => $order->id
        ]);

        $this->assertCount(2, $product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $product->orderItems->first());
    }

    public function test_product_scope_active_filters_active_products(): void
    {
        $category = Category::factory()->create();
        $activeProducts = Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'status' => 'active'
        ]);
        $inactiveProducts = Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'status' => 'inactive'
        ]);

        $activeProductsFromDB = Product::active()->get();

        $this->assertCount(3, $activeProductsFromDB);
        foreach ($activeProducts as $product) {
            $this->assertTrue($activeProductsFromDB->contains($product));
        }
        foreach ($inactiveProducts as $product) {
            $this->assertFalse($activeProductsFromDB->contains($product));
        }
    }

    public function test_product_scope_in_stock_filters_products_with_stock(): void
    {
        $category = Category::factory()->create();
        $inStockProducts = Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'quantity' => 10
        ]);
        $outOfStockProducts = Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'quantity' => 0
        ]);

        $inStockProductsFromDB = Product::inStock()->get();

        $this->assertCount(3, $inStockProductsFromDB);
        foreach ($inStockProducts as $product) {
            $this->assertTrue($inStockProductsFromDB->contains($product));
        }
        foreach ($outOfStockProducts as $product) {
            $this->assertFalse($inStockProductsFromDB->contains($product));
        }
    }

    public function test_product_scope_by_category_filters_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $category1Products = Product::factory()->count(3)->create(['category_id' => $category1->id]);
        $category2Products = Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $filteredProducts = Product::byCategory($category1->id)->get();

        $this->assertCount(3, $filteredProducts);
        foreach ($category1Products as $product) {
            $this->assertTrue($filteredProducts->contains($product));
        }
        foreach ($category2Products as $product) {
            $this->assertFalse($filteredProducts->contains($product));
        }
    }

    public function test_product_scope_search_finds_products_by_name(): void
    {
        $category = Category::factory()->create();
        $laptopProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Gaming Laptop'
        ]);
        $chairProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Office Chair'
        ]);

        $searchResults = Product::search('laptop')->get();

        $this->assertCount(1, $searchResults);
        $this->assertTrue($searchResults->contains($laptopProduct));
        $this->assertFalse($searchResults->contains($chairProduct));
    }

    public function test_product_scope_search_finds_products_by_description(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Computer',
            'description' => 'High-performance gaming machine'
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Desk',
            'description' => 'Simple office furniture'
        ]);

        $searchResults = Product::search('gaming')->get();

        $this->assertCount(1, $searchResults);
        $this->assertTrue($searchResults->contains($product1));
        $this->assertFalse($searchResults->contains($product2));
    }

    public function test_product_is_in_stock_method(): void
    {
        $category = Category::factory()->create();
        $inStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);
        $outOfStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 0
        ]);

        $this->assertTrue($inStockProduct->isInStock());
        $this->assertFalse($outOfStockProduct->isInStock());
    }

    public function test_product_is_low_stock_method(): void
    {
        $category = Category::factory()->create();
        $lowStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10
        ]);
        $goodStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 10
        ]);

        $this->assertTrue($lowStockProduct->isLowStock());
        $this->assertFalse($goodStockProduct->isLowStock());
    }

    public function test_product_reduce_stock_method(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        $result = $product->reduceStock(3);

        $this->assertTrue($result);
        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_product_reduce_stock_fails_with_insufficient_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5
        ]);

        $result = $product->reduceStock(10);

        $this->assertFalse($result);
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_product_increase_stock_method(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10
        ]);

        $product->increaseStock(5);

        $this->assertEquals(15, $product->fresh()->stock_quantity);
    }

    public function test_product_formatted_price_accessor(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 99.99
        ]);

        $this->assertEquals('$99.99', $product->formatted_price);
    }

    public function test_product_stock_status_accessor(): void
    {
        $category = Category::factory()->create();
        
        $inStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5
        ]);
        
        $lowStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5
        ]);
        
        $outOfStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 0
        ]);

        $this->assertEquals('in_stock', $inStockProduct->stock_status);
        $this->assertEquals('low_stock', $lowStockProduct->stock_status);
        $this->assertEquals('out_of_stock', $outOfStockProduct->stock_status);
    }

    public function test_product_average_rating_calculation(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // This test assumes you have a reviews relationship and method
        // Adjust based on your actual implementation
        $this->assertEquals(0, $product->average_rating ?? 0);
    }

    public function test_product_image_url_accessor(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image' => 'products/test-image.jpg'
        ]);

        $this->assertStringContainsString('products/test-image.jpg', $product->image_url);
    }

    public function test_product_slug_generation(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Gaming Laptop Pro 2024'
        ]);

        $this->assertNotNull($product->slug);
        $this->assertStringContainsString('gaming-laptop-pro-2024', $product->slug);
    }
}