<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external APIs and services used by SmartMart including
    | AI services, SMS providers, search engines, and other integrations.
    |
    */

    // AI Services
    'ai' => [
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct:free'),
            'timeout' => 30,
            'max_tokens' => 1000,
        ],
    ],

    // SMS Services
    'sms' => [
        'default' => env('SMS_PROVIDER', 'twilio'),
        
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
            'enabled' => env('TWILIO_ENABLED', false),
        ],

        'nexmo' => [
            'key' => env('NEXMO_KEY'),
            'secret' => env('NEXMO_SECRET'),
            'from' => env('NEXMO_FROM'),
            'enabled' => env('NEXMO_ENABLED', false),
        ],
    ],

    // Search Engine
    'search' => [
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
            'key' => env('MEILISEARCH_KEY'),
            'enabled' => env('MEILISEARCH_ENABLED', true),
            'indexes' => [
                'products' => [
                    'primary_key' => 'id',
                    'searchable_attributes' => ['name', 'description', 'tags', 'brand'],
                    'filterable_attributes' => ['category_id', 'price', 'in_stock', 'brand'],
                    'sortable_attributes' => ['price', 'created_at', 'popularity_score'],
                ],
                'categories' => [
                    'primary_key' => 'id',
                    'searchable_attributes' => ['name', 'description'],
                    'filterable_attributes' => ['parent_id', 'is_active'],
                ],
            ],
        ],
    ],

    // Social Media Integration
    'social' => [
        'facebook' => [
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'enabled' => env('FACEBOOK_ENABLED', false),
        ],

        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'enabled' => env('GOOGLE_ENABLED', false),
        ],

        'twitter' => [
            'client_id' => env('TWITTER_CLIENT_ID'),
            'client_secret' => env('TWITTER_CLIENT_SECRET'),
            'enabled' => env('TWITTER_ENABLED', false),
        ],
    ],

    // Email Marketing
    'email_marketing' => [
        'mailchimp' => [
            'api_key' => env('MAILCHIMP_API_KEY'),
            'list_id' => env('MAILCHIMP_LIST_ID'),
            'enabled' => env('MAILCHIMP_ENABLED', false),
        ],

        'sendgrid' => [
            'api_key' => env('SENDGRID_API_KEY'),
            'enabled' => env('SENDGRID_ENABLED', false),
        ],
    ],

    // Analytics & Tracking
    'analytics' => [
        'google_analytics' => [
            'tracking_id' => env('GA_TRACKING_ID'),
            'enabled' => env('GA_ENABLED', false),
        ],

        'facebook_pixel' => [
            'pixel_id' => env('FACEBOOK_PIXEL_ID'),
            'enabled' => env('FACEBOOK_PIXEL_ENABLED', false),
        ],
    ],

    // Shipping Providers
    'shipping' => [
        'ups' => [
            'api_key' => env('UPS_API_KEY'),
            'user_id' => env('UPS_USER_ID'),
            'password' => env('UPS_PASSWORD'),
            'enabled' => env('UPS_ENABLED', false),
        ],

        'fedex' => [
            'key' => env('FEDEX_KEY'),
            'password' => env('FEDEX_PASSWORD'),
            'account' => env('FEDEX_ACCOUNT'),
            'meter' => env('FEDEX_METER'),
            'enabled' => env('FEDEX_ENABLED', false),
        ],

        'usps' => [
            'username' => env('USPS_USERNAME'),
            'password' => env('USPS_PASSWORD'),
            'enabled' => env('USPS_ENABLED', false),
        ],
    ],

    // Content Delivery Network
    'cdn' => [
        'cloudflare' => [
            'zone_id' => env('CLOUDFLARE_ZONE_ID'),
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            'enabled' => env('CLOUDFLARE_ENABLED', false),
        ],

        'aws_cloudfront' => [
            'distribution_id' => env('AWS_CLOUDFRONT_DISTRIBUTION_ID'),
            'enabled' => env('AWS_CLOUDFRONT_ENABLED', false),
        ],
    ],

    // Backup Services
    'backup' => [
        's3' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BACKUP_BUCKET'),
            'enabled' => env('S3_BACKUP_ENABLED', false),
        ],
    ],
];