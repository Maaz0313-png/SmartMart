<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\RolesAndPermissionsSeeder;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for all tests
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Create a user with specific role
     */
    protected function createUser(string $role = 'buyer', array $attributes = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Create an admin user
     */
    protected function createAdmin(array $attributes = []): \App\Models\User
    {
        return $this->createUser('admin', $attributes);
    }

    /**
     * Create a seller user
     */
    protected function createSeller(array $attributes = []): \App\Models\User
    {
        return $this->createUser('seller', $attributes);
    }

    /**
     * Create a buyer user
     */
    protected function createBuyer(array $attributes = []): \App\Models\User
    {
        return $this->createUser('buyer', $attributes);
    }

    /**
     * Act as a user with specific role
     */
    protected function actingAsUser(string $role = 'buyer', array $attributes = []): \App\Models\User
    {
        $user = $this->createUser($role, $attributes);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Act as admin
     */
    protected function actingAsAdmin(array $attributes = []): \App\Models\User
    {
        return $this->actingAsUser('admin', $attributes);
    }

    /**
     * Act as seller
     */
    protected function actingAsSeller(array $attributes = []): \App\Models\User
    {
        return $this->actingAsUser('seller', $attributes);
    }

    /**
     * Act as buyer
     */
    protected function actingAsBuyer(array $attributes = []): \App\Models\User
    {
        return $this->actingAsUser('buyer', $attributes);
    }

    /**
     * Create API token for user
     */
    protected function createApiToken(\App\Models\User $user, array $abilities = []): string
    {
        if (empty($abilities)) {
            $abilities = $user->hasRole('admin') ? ['*'] : ['basic'];
        }
        
        return $user->createToken('test-token', $abilities)->plainTextToken;
    }

    /**
     * Make API request with token
     */
    protected function apiAs(\App\Models\User $user, string $method, string $uri, array $data = [], array $headers = [])
    {
        $token = $this->createApiToken($user);
        $headers['Authorization'] = 'Bearer ' . $token;
        
        return $this->json($method, $uri, $data, $headers);
    }
}
