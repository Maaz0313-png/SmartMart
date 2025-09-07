<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for various payment providers including Stripe, PayPal,
    | and other payment gateways supported by SmartMart.
    |
    */

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'gateways' => [
        'stripe' => [
            'driver' => 'stripe',
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'USD'),
            'enabled' => env('STRIPE_ENABLED', true),
            'webhooks' => [
                'payment_intent.succeeded',
                'invoice.payment_succeeded',
                'invoice.payment_failed',
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
            ],
        ],

        'paypal' => [
            'driver' => 'paypal',
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'app_id' => env('PAYPAL_APP_ID'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
            'enabled' => env('PAYPAL_ENABLED', true),
            'currency' => env('PAYPAL_CURRENCY', 'USD'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ],

        'cod' => [
            'driver' => 'cash_on_delivery',
            'enabled' => env('COD_ENABLED', true),
            'fee' => env('COD_FEE', 5.00),
            'max_amount' => env('COD_MAX_AMOUNT', 1000),
            'countries' => ['US', 'CA', 'GB'], // Allowed countries
        ],
    ],

    // Common payment settings
    'settings' => [
        'capture_method' => 'automatic', // automatic or manual
        'confirmation_method' => 'automatic',
        'save_payment_methods' => true,
        'allow_partial_payments' => false,
        'refund_policy_days' => 30,
    ],

    // Transaction fees
    'fees' => [
        'stripe' => [
            'percentage' => 2.9,
            'fixed' => 0.30,
        ],
        'paypal' => [
            'percentage' => 3.49,
            'fixed' => 0.49,
        ],
        'cod' => [
            'percentage' => 0,
            'fixed' => 5.00,
        ],
    ],

    // Security settings
    'security' => [
        'require_cvv' => true,
        'require_postal_code' => true,
        'fraud_detection' => true,
        'max_payment_attempts' => 3,
        'payment_timeout' => 900, // 15 minutes
    ],
];