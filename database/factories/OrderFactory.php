<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . $this->faker->unique()->randomNumber(8),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'subtotal' => $this->faker->randomFloat(2, 10, 900),
            'tax_amount' => $this->faker->randomFloat(2, 0, 100),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 50),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal', 'cod']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'shipping_address' => [
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'address_line_1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->country(),
            ],
            'billing_address' => [
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'address_line_1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->country(),
            ],
        ];
    }
}
