<?php

namespace Database\Factories;

use App\Models\DataRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataRequest>
 */
class DataRequestFactory extends Factory
{
    protected $model = DataRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['export', 'delete']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'requested_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'completed_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-2 weeks', 'now') : null,
            'expires_at' => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('now', '+30 days') : null,
            'file_path' => $this->faker->boolean(30) ? 'gdpr/exports/user_data_' . $this->faker->randomNumber(5) . '.json' : null,
            'export_file_path' => $this->faker->boolean(30) ? 'exports/user_data_' . $this->faker->randomNumber(5) . '.json' : null,
            'reason' => $this->faker->optional()->sentence(),
            'metadata' => [
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'request_source' => $this->faker->randomElement(['web', 'mobile', 'api']),
            ],
        ];
    }

    /**
     * Indicate that the request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'expires_at' => null,
            'file_path' => null,
        ]);
    }

    /**
     * Indicate that the request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the request is for data export.
     */
    public function export(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'export',
        ]);
    }

    /**
     * Indicate that the request is for data deletion.
     */
    public function delete(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'delete',
        ]);
    }
}
