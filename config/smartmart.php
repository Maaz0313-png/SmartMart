<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SmartMart Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains SmartMart-specific configuration options for
    | e-commerce functionality, AI features, and business logic.
    |
    */

    // AI Configuration
    'ai' => [
        'recommendations' => [
            'engine' => 'openrouter',
            'max_products' => 20,
            'cache_ttl' => 3600, // 1 hour
        ],
        'content_generation' => [
            'enabled' => true,
            'max_length' => 500,
        ],
    ],

    // E-commerce Settings
    'shop' => [
        'currency' => env('SHOP_CURRENCY', 'USD'),
        'tax_rate' => env('SHOP_TAX_RATE', 0.08), // 8% default
        'free_shipping_threshold' => env('SHOP_FREE_SHIPPING_THRESHOLD', 100),
        'inventory_tracking' => true,
        'low_stock_threshold' => 5,
    ],

    // Subscription Settings
    'subscriptions' => [
        'trial_days' => 7,
        'billing_cycles' => ['weekly', 'monthly', 'quarterly', 'yearly'],
        'cancellation_grace_period' => 3, // days
        'max_pause_duration' => 90, // days
    ],

    // Order Management
    'orders' => [
        'number_prefix' => 'SM',
        'number_length' => 8,
        'auto_fulfill_digital' => true,
        'refund_window' => 30, // days
        'statuses' => [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ],
    ],

    // Search & Analytics
    'search' => [
        'engine' => 'meilisearch',
        'results_per_page' => 24,
        'max_suggestions' => 5,
        'trending_days' => 7,
    ],

    // File Storage
    'media' => [
        'disk' => env('FILESYSTEM_DISK', 'public'),
        'max_file_size' => 5120, // KB (5MB)
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 800],
        ],
    ],

    // Security Settings
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'password_reset_expires' => 3600, // 1 hour
        'otp_expires' => 300, // 5 minutes
        'session_lifetime' => 120, // minutes
    ],

    // Notification Settings
    'notifications' => [
        'channels' => ['mail', 'database', 'sms'],
        'queue_notifications' => true,
        'retry_failed' => 3,
        'batch_size' => 100,
    ],

    // API Settings
    'api' => [
        'rate_limit' => 60, // requests per minute
        'versions' => ['v1', 'v2'],
        'default_version' => 'v1',
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
    ],

    // Analytics
    'analytics' => [
        'track_user_behavior' => true,
        'track_search_queries' => true,
        'retention_days' => 365,
        'dashboard_cache_ttl' => 300, // 5 minutes
    ],
];