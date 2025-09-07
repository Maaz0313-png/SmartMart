<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1 routes
Route::prefix('v1')->group(function () {
    
    // Authentication routes (no auth required)
    Route::prefix('auth')->group(function () {
        Route::post('register', [ApiAuthController::class, 'register']);
        Route::post('login', [ApiAuthController::class, 'login']);
    });

    // Protected routes requiring authentication
    Route::middleware('auth:sanctum')->group(function () {
        
        // Authentication management
        Route::prefix('auth')->group(function () {
            Route::get('user', [ApiAuthController::class, 'user']);
            Route::post('logout', [ApiAuthController::class, 'logout']);
            Route::post('logout-all', [ApiAuthController::class, 'logoutAll']);
            Route::post('refresh', [ApiAuthController::class, 'refresh']);
            Route::get('tokens', [ApiAuthController::class, 'tokens']);
            Route::delete('tokens/{tokenId}', [ApiAuthController::class, 'revokeToken']);
        });

        // Products API (requires basic ability)
        Route::middleware('abilities:basic')->group(function () {
            Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class)->only(['index', 'show']);
            Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class)->only(['index', 'show']);
        });

        // Cart API (requires cart:manage ability)
        Route::middleware('abilities:cart:manage')->group(function () {
            Route::apiResource('cart', \App\Http\Controllers\Api\CartController::class)->except(['show']);
        });

        // Orders API
        Route::middleware('abilities:orders:view')->group(function () {
            Route::get('orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
            Route::get('orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
        });

        Route::middleware('abilities:orders:create')->group(function () {
            Route::post('orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
        });

        // Reviews API
        Route::middleware('abilities:reviews:create')->group(function () {
            Route::post('reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
        });

        // Seller routes (requires seller-specific abilities)
        Route::middleware('abilities:products:create,products:update,products:delete')->group(function () {
            Route::apiResource('seller/products', \App\Http\Controllers\Api\Seller\ProductController::class)
                ->except(['index', 'show']);
        });

        // Admin routes (requires admin abilities)
        Route::middleware('abilities:*')->prefix('admin')->group(function () {
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            Route::apiResource('products', \App\Http\Controllers\Api\Admin\ProductController::class);
            Route::apiResource('orders', \App\Http\Controllers\Api\Admin\OrderController::class);
            Route::get('analytics', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'index']);
        });
    });
});

// API v2 routes (future expansion)
Route::prefix('v2')->group(function () {
    // Future API versions can be added here
});

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => config('app.version', '1.0.0'),
    ]);
});