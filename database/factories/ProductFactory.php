<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 1000);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->randomNumber(4),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(),
            'sku' => 'SKU-' . $this->faker->unique()->randomNumber(8),
            'price' => $price,
            'compare_price' => $this->faker->boolean(30) ? $price + $this->faker->randomFloat(2, 10, 50) : null,
            'cost_price' => $price * 0.7,
            'quantity' => $this->faker->numberBetween(0, 100),
            'min_quantity' => $this->faker->numberBetween(0, 5),
            'track_quantity' => $this->faker->boolean(80),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'images' => [
                'products/' . $this->faker->word() . '.jpg',
                'products/' . $this->faker->word() . '.jpg',
            ],
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'weight_unit' => 'kg',
            'dimensions' => [
                'length' => $this->faker->randomFloat(2, 1, 50),
                'width' => $this->faker->randomFloat(2, 1, 50),
                'height' => $this->faker->randomFloat(2, 1, 50),
            ],
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'tags' => $this->faker->words(5),
            'meta_data' => [
                'brand' => $this->faker->company(),
                'model' => $this->faker->word(),
                'color' => $this->faker->colorName(),
            ],
            'is_featured' => $this->faker->boolean(20),
            'is_digital' => $this->faker->boolean(10),
            'seo_data' => [
                'meta_title' => $name,
                'meta_description' => $this->faker->sentence(),
                'meta_keywords' => implode(',', $this->faker->words(5)),
            ],
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the product is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 100),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
