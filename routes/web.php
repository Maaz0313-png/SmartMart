<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy-policy', function () {
    return Inertia::render('Privacy/Policy');
})->name('privacy.policy');
Route::get('/terms-of-service', function () {
    return Inertia::render('Legal/Terms');
})->name('terms.service');

// Guest routes (authentication)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    // OTP routes
    Route::get('verify-otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('send-otp', [OtpController::class, 'send'])->name('otp.send');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Cart routes
    Route::controller(\App\Http\Controllers\CartController::class)->group(function () {
        Route::get('/cart', 'index')->name('cart.index');
        Route::post('/cart', 'store')->name('cart.store');
        Route::patch('/cart/{cartItem}', 'update')->name('cart.update');
        Route::delete('/cart/{cartItem}', 'destroy')->name('cart.destroy');
        Route::delete('/cart', 'clear')->name('cart.clear');
        Route::get('/cart/count', 'count')->name('cart.count');
        Route::post('/cart/coupon', 'applyCoupon')->name('cart.coupon');
    });

    // Checkout routes
    Route::controller(\App\Http\Controllers\CheckoutController::class)->group(function () {
        Route::get('/checkout', 'index')->name('checkout.index');
        Route::post('/checkout', 'process')->name('checkout.process');
        Route::get('/checkout/confirmation/{order}', 'confirmation')->name('checkout.confirmation');
    });

    // Order routes
    Route::controller(\App\Http\Controllers\OrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('orders.index');
        Route::get('/orders/{order}', 'show')->name('orders.show');
        Route::post('/orders/{order}/cancel', 'cancel')->name('orders.cancel');
        Route::post('/orders/{order}/refund', 'requestRefund')->name('orders.refund');
        Route::post('/orders/{order}/reorder', 'reorder')->name('orders.reorder');
        Route::get('/orders/{order}/invoice', 'downloadInvoice')->name('orders.invoice');
        Route::get('/orders/{order}/track', 'track')->name('orders.track');
    });

    // Subscription routes
    Route::controller(\App\Http\Controllers\SubscriptionController::class)->group(function () {
        Route::get('/subscriptions/plans', 'plans')->name('subscriptions.plans');
        Route::get('/subscriptions/checkout/{plan}', 'checkout')->name('subscriptions.checkout');
        Route::post('/subscriptions/{plan}', 'store')->name('subscriptions.store');
        Route::get('/subscriptions/manage', 'manage')->name('subscriptions.manage');
        Route::patch('/subscriptions/{subscription}/preferences', 'updatePreferences')->name('subscriptions.preferences');
        Route::post('/subscriptions/{subscription}/pause', 'pause')->name('subscriptions.pause');
        Route::post('/subscriptions/{subscription}/resume', 'resume')->name('subscriptions.resume');
        Route::post('/subscriptions/{subscription}/cancel', 'cancel')->name('subscriptions.cancel');
        Route::post('/subscriptions/{subscription}/reactivate', 'reactivate')->name('subscriptions.reactivate');
        Route::get('/subscriptions/history', 'history')->name('subscriptions.history');
        Route::get('/subscriptions/{subscription}/boxes', 'boxes')->name('subscriptions.boxes');
    });

    // Product routes
    Route::controller(\App\Http\Controllers\ProductController::class)->group(function () {
        Route::get('/products', 'index')->name('products.index');
        Route::get('/products/{product}', 'show')->name('products.show');
        Route::get('/search', 'search')->name('products.search');
    });

    // Notification routes
    Route::controller(\App\Http\Controllers\NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{notification}/mark-read', 'markAsRead')->name('mark-read');
        Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
        Route::get('/unread-count', 'unreadCount')->name('unread-count');
        Route::delete('/{notification}', 'destroy')->name('destroy');
        Route::get('/preferences', 'preferences')->name('preferences');
        Route::post('/preferences', 'updatePreferences')->name('preferences.update');
        Route::get('/recent', 'recent')->name('recent');
    });

    // GDPR/Privacy routes
    Route::controller(\App\Http\Controllers\GdprController::class)->prefix('privacy')->name('gdpr.')->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::post('/export', 'requestDataExport')->name('export');
        Route::post('/delete', 'requestDataDeletion')->name('delete');
        Route::get('/download/{dataRequest}', 'downloadExport')->name('download');
        Route::post('/consent', 'recordConsent')->name('consent');
    });
});

// Role-based routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('dashboard');

    // User Management
    Route::controller(\App\Http\Controllers\Admin\UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}', 'show')->name('show');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::patch('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::post('/{user}/impersonate', 'impersonate')->name('impersonate');
        Route::post('/stop-impersonating', 'stopImpersonating')->name('stop-impersonating');
        Route::get('/{user}/activity', 'activity')->name('activity');
    });

    // Product Management
    Route::controller(\App\Http\Controllers\Admin\ProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product}', 'show')->name('show');
        Route::get('/{product}/edit', 'edit')->name('edit');
        Route::patch('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::post('/import', 'import')->name('import');
        Route::get('/export', 'export')->name('export');
        Route::patch('/{product}/inventory', 'updateInventory')->name('update-inventory');
    });

    // Order Management
    Route::controller(\App\Http\Controllers\Admin\OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{order}', 'show')->name('show');
        Route::patch('/{order}/status', 'updateStatus')->name('update-status');
        Route::post('/{order}/ship', 'markAsShipped')->name('ship');
        Route::post('/bulk-update', 'bulkUpdate')->name('bulk-update');
        Route::get('/analytics', 'analytics')->name('analytics');
    });

    // Subscription Management
    Route::controller(\App\Http\Controllers\Admin\SubscriptionController::class)->prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{subscription}', 'show')->name('show');
        Route::post('/{subscription}/pause', 'pause')->name('pause');
        Route::post('/{subscription}/resume', 'resume')->name('resume');
        Route::post('/{subscription}/cancel', 'cancel')->name('cancel');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::get('/analytics', 'analytics')->name('analytics');
    });

    // Category Management
    Route::controller(\App\Http\Controllers\Admin\CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{category}', 'show')->name('show');
        Route::get('/{category}/edit', 'edit')->name('edit');
        Route::patch('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::post('/reorder', 'reorder')->name('reorder');
        Route::get('/tree/view', 'getTree')->name('tree');
    });

    // Analytics
    Route::controller(\App\Http\Controllers\Admin\AnalyticsController::class)->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', 'overview')->name('overview');
        Route::get('/sales', 'sales')->name('sales');
        Route::get('/products', 'products')->name('products');
        Route::get('/customers', 'customers')->name('customers');
    });

    // Settings
    Route::controller(\App\Http\Controllers\Admin\SettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/', 'update')->name('update');
        Route::post('/upload-logo', 'uploadLogo')->name('upload-logo');
        Route::post('/test-mail', 'testMailConfiguration')->name('test-mail');
        Route::post('/clear-cache', 'clearCache')->name('clear-cache');
        Route::get('/export', 'exportSettings')->name('export');
        Route::post('/import', 'importSettings')->name('import');
    });

    // GDPR Management
    Route::controller(\App\Http\Controllers\Admin\GdprController::class)->prefix('privacy')->name('gdpr.')->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/requests', 'index')->name('index');
        Route::get('/requests/{dataRequest}', 'show')->name('show');
        Route::patch('/requests/{dataRequest}', 'update')->name('update');
        Route::post('/requests/bulk', 'bulkUpdate')->name('bulk-update');
        Route::get('/export', 'export')->name('export');
    });
});

Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    // Seller Dashboard
    Route::get('/', function () {
        return Inertia::render('Seller/Dashboard');
    })->name('dashboard');

    // Seller Product Management
    Route::controller(\App\Http\Controllers\Seller\ProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{product}', 'show')->name('show');
        Route::get('/{product}/edit', 'edit')->name('edit');
        Route::patch('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
        Route::post('/bulk-action', 'bulkAction')->name('bulk-action');
        Route::post('/{product}/duplicate', 'duplicate')->name('duplicate');
        Route::patch('/{product}/inventory', 'updateInventory')->name('update-inventory');
    });
});

// Email verification
Route::middleware('auth')->group(function () {
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Webhook routes (no auth required)
Route::post('webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->name('webhooks.stripe');

// Catch-all route for Inertia SPA (keep this at the end)
// Route::get('/{any}', function () {
//     return Inertia::render('Welcome');
// })->where('any', '.*');
