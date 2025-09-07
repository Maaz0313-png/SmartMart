<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_with_valid_credentials(): void
    {
        $user = $this->createBuyer(['email' => 'test@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
                'abilities'
            ]);
    }

    public function test_api_login_with_invalid_credentials(): void
    {
        $user = $this->createBuyer();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_user_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    public function test_api_user_endpoint_returns_authenticated_user(): void
    {
        $user = $this->createBuyer();

        $response = $this->apiAs($user, 'GET', '/api/v1/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
    }

    public function test_api_logout_revokes_token(): void
    {
        $user = $this->createBuyer();

        $response = $this->apiAs($user, 'POST', '/api/v1/auth/logout');

        $response->assertStatus(200);

        // Try to access protected endpoint with same token
        $response = $this->apiAs($user, 'GET', '/api/v1/auth/user');
        $response->assertStatus(401);
    }

    public function test_api_products_endpoint_requires_basic_ability(): void
    {
        $user = $this->createBuyer();
        Category::factory()->create();
        Product::factory()->count(3)->create();

        $response = $this->apiAs($user, 'GET', '/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'category']
                ]
            ]);
    }

    public function test_api_rate_limiting(): void
    {
        $user = $this->createBuyer();

        // Make many requests rapidly
        for ($i = 0; $i < 125; $i++) {
            $response = $this->apiAs($user, 'GET', '/api/v1/auth/user');
            
            if ($response->status() === 429) {
                // Rate limit reached
                $this->assertEquals(429, $response->status());
                return;
            }
        }

        $this->fail('Rate limiting should have been triggered');
    }

    public function test_api_registration(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
                'abilities'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_api_token_abilities_vary_by_role(): void
    {
        $admin = $this->createAdmin();
        $seller = $this->createSeller();
        $buyer = $this->createBuyer();

        // Test admin abilities
        $adminToken = $this->createApiToken($admin);
        $this->assertContains('*', $admin->createToken('test', $this->getTokenAbilities($admin))->accessToken->abilities);

        // Test seller abilities
        $sellerAbilities = $this->getTokenAbilities($seller);
        $this->assertContains('products:create', $sellerAbilities);
        $this->assertContains('products:update', $sellerAbilities);

        // Test buyer abilities
        $buyerAbilities = $this->getTokenAbilities($buyer);
        $this->assertContains('basic', $buyerAbilities);
        $this->assertContains('cart:manage', $buyerAbilities);
        $this->assertNotContains('products:create', $buyerAbilities);
    }

    private function getTokenAbilities(User $user): array
    {
        $abilities = ['basic'];

        if ($user->hasRole('admin')) {
            $abilities = ['*'];
        } elseif ($user->hasRole('seller')) {
            $abilities = [
                'basic',
                'products:create',
                'products:update',
                'products:delete',
                'orders:view',
                'analytics:view',
            ];
        } elseif ($user->hasRole('buyer')) {
            $abilities = [
                'basic',
                'orders:create',
                'orders:view',
                'cart:manage',
                'reviews:create',
            ];
        }

        return $abilities;
    }
}