<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocController extends Controller
{
    /**
     * Get API documentation and endpoints
     */
    public function index()
    {
        return response()->json([
            'api_version' => 'v1',
            'documentation_url' => url('/api/docs'),
            'endpoints' => $this->getApiEndpoints(),
            'authentication' => $this->getAuthenticationInfo(),
            'rate_limits' => $this->getRateLimitInfo(),
            'response_format' => $this->getResponseFormatInfo(),
        ]);
    }

    /**
     * Get available API endpoints
     */
    private function getApiEndpoints(): array
    {
        return [
            'authentication' => [
                'POST /api/v1/auth/register' => 'Register a new user',
                'POST /api/v1/auth/login' => 'Login user and get token',
                'GET /api/v1/auth/user' => 'Get authenticated user info',
                'POST /api/v1/auth/logout' => 'Logout current device',
                'POST /api/v1/auth/logout-all' => 'Logout all devices',
                'POST /api/v1/auth/refresh' => 'Refresh API token',
                'GET /api/v1/auth/tokens' => 'List user tokens',
                'DELETE /api/v1/auth/tokens/{id}' => 'Revoke specific token',
            ],
            'products' => [
                'GET /api/v1/products' => 'List products with pagination',
                'GET /api/v1/products/{id}' => 'Get specific product details',
                'GET /api/v1/categories' => 'List product categories',
                'GET /api/v1/categories/{id}' => 'Get specific category',
            ],
            'cart' => [
                'GET /api/v1/cart' => 'Get user cart items',
                'POST /api/v1/cart' => 'Add item to cart',
                'PUT /api/v1/cart/{id}' => 'Update cart item quantity',
                'DELETE /api/v1/cart/{id}' => 'Remove item from cart',
            ],
            'orders' => [
                'GET /api/v1/orders' => 'List user orders',
                'GET /api/v1/orders/{id}' => 'Get order details',
                'POST /api/v1/orders' => 'Create new order',
            ],
            'reviews' => [
                'POST /api/v1/reviews' => 'Create product review',
            ],
            'seller' => [
                'POST /api/v1/seller/products' => 'Create product (sellers only)',
                'PUT /api/v1/seller/products/{id}' => 'Update product (sellers only)',
                'DELETE /api/v1/seller/products/{id}' => 'Delete product (sellers only)',
            ],
            'admin' => [
                'GET /api/v1/admin/users' => 'List all users (admin only)',
                'POST /api/v1/admin/users' => 'Create user (admin only)',
                'GET /api/v1/admin/users/{id}' => 'Get user details (admin only)',
                'PUT /api/v1/admin/users/{id}' => 'Update user (admin only)',
                'DELETE /api/v1/admin/users/{id}' => 'Delete user (admin only)',
                'GET /api/v1/admin/products' => 'List all products (admin only)',
                'GET /api/v1/admin/orders' => 'List all orders (admin only)',
                'GET /api/v1/admin/analytics' => 'Get analytics data (admin only)',
            ],
        ];
    }

    /**
     * Get authentication information
     */
    private function getAuthenticationInfo(): array
    {
        return [
            'type' => 'Bearer Token',
            'header' => 'Authorization: Bearer {token}',
            'obtain_token' => 'POST /api/v1/auth/login',
            'token_abilities' => [
                'admin' => ['*'], // All abilities
                'seller' => ['basic', 'products:create', 'products:update', 'products:delete', 'orders:view', 'analytics:view'],
                'buyer' => ['basic', 'orders:create', 'orders:view', 'cart:manage', 'reviews:create'],
            ],
        ];
    }

    /**
     * Get rate limit information
     */
    private function getRateLimitInfo(): array
    {
        return [
            'general_endpoints' => '120 requests per minute',
            'auth_endpoints' => '5 requests per minute',
            'headers' => [
                'X-RateLimit-Limit' => 'Maximum requests allowed',
                'X-RateLimit-Remaining' => 'Remaining requests',
                'Retry-After' => 'Seconds to wait when limit exceeded',
            ],
        ];
    }

    /**
     * Get response format information
     */
    private function getResponseFormatInfo(): array
    {
        return [
            'success_format' => [
                'data' => 'Response data',
                'message' => 'Success message (optional)',
                'meta' => 'Pagination/additional metadata (optional)',
            ],
            'error_format' => [
                'message' => 'Error message',
                'errors' => 'Validation errors (optional)',
            ],
            'status_codes' => [
                '200' => 'Success',
                '201' => 'Created',
                '401' => 'Unauthorized',
                '403' => 'Forbidden',
                '404' => 'Not Found',
                '422' => 'Validation Error',
                '429' => 'Too Many Requests',
                '500' => 'Server Error',
            ],
        ];
    }
}