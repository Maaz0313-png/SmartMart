<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search engine that will be used by the
    | Scout search functionality. This engine will be used to search across
    | all searchable models unless a specific engine is configured for
    | individual models.
    |
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records by their
    | primary keys.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an
    | open-source search engine. Meilisearch's main goal is to provide a
    | great search experience for end users in a simple way to deploy.
    |
    | See: https://docs.meilisearch.com/learn/configuration/instance_options.html
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'products' => [
                'searchableAttributes' => ['name', 'description', 'tags', 'brand', 'sku'],
                'filterableAttributes' => [
                    'category_id', 
                    'price', 
                    'in_stock', 
                    'brand', 
                    'status',
                    'seller_id',
                    'created_at'
                ],
                'sortableAttributes' => [
                    'price', 
                    'created_at', 
                    'popularity_score',
                    'rating',
                    'sales_count'
                ],
                'displayedAttributes' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'image_url',
                    'brand',
                    'category_id',
                    'in_stock',
                    'rating',
                    'slug'
                ],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'popularity_score:desc',
                    'rating:desc'
                ],
                'synonyms' => [
                    'phone' => ['smartphone', 'mobile'],
                    'laptop' => ['notebook', 'computer'],
                    'tv' => ['television'],
                ],
                'stopWords' => ['the', 'a', 'an'],
            ],
            'categories' => [
                'searchableAttributes' => ['name', 'description'],
                'filterableAttributes' => ['parent_id', 'is_active', 'level'],
                'sortableAttributes' => ['name', 'sort_order'],
                'displayedAttributes' => [
                    'id',
                    'name', 
                    'description',
                    'slug',
                    'parent_id',
                    'is_active'
                ],
            ],
            'users' => [
                'searchableAttributes' => ['name', 'email'],
                'filterableAttributes' => ['role', 'email_verified_at', 'created_at'],
                'sortableAttributes' => ['name', 'created_at'],
                'displayedAttributes' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at'
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud
    | hosted search engine which provides a great search experience.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

];