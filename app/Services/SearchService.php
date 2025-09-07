<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Meilisearch\Client;

class SearchService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );
    }

    /**
     * Search products with advanced filters and sorting.
     */
    public function searchProducts(array $params): array
    {
        $query = $params['q'] ?? '';
        $filters = $this->buildFilters($params);
        $sort = $this->buildSort($params['sort'] ?? 'relevance');

        $searchParams = [
            'limit' => $params['limit'] ?? 24,
            'offset' => $params['offset'] ?? 0,
            'attributesToRetrieve' => [
                'id',
                'name',
                'slug',
                'price',
                'compare_price',
                'images',
                'category_id',
                'user_id',
                'is_featured',
                'quantity',
                'track_quantity'
            ],
            'attributesToHighlight' => ['name', 'description'],
            'facets' => ['category_id', 'price', 'is_featured', 'brand'],
        ];

        if (!empty($filters)) {
            $searchParams['filter'] = $filters;
        }

        if (!empty($sort)) {
            $searchParams['sort'] = $sort;
        }

        try {
            $index = $this->client->index('products');
            $results = $index->search($query, $searchParams);

            return [
                'hits' => $results->getHits(),
                'total' => $results->getHitsCount(),
                'facets' => $results->getFacetDistribution(),
                'processing_time' => $results->getProcessingTimeMs(),
            ];
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return [
                'hits' => [],
                'total' => 0,
                'facets' => [],
                'processing_time' => 0,
            ];
        }
    }

    /**
     * Get search suggestions.
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        $cacheKey = 'search_suggestions_' . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query, $limit) {
            try {
                $index = $this->client->index('products');
                $results = $index->search($query, [
                    'limit' => $limit,
                    'attributesToRetrieve' => ['name'],
                ]);

                return collect($results->getHits())
                    ->pluck('name')
                    ->unique()
                    ->take($limit)
                    ->values()
                    ->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    /**
     * Track search query for analytics.
     */
    public function trackSearch(string $query, ?User $user = null): void
    {
        // Store search analytics
        \DB::table('search_analytics')->insert([
            'query' => $query,
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get trending searches.
     */
    public function getTrendingSearches(int $limit = 10): array
    {
        $cacheKey = 'trending_searches';

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            return \DB::table('search_analytics')
                ->select('query', \DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit($limit)
                ->pluck('query')
                ->toArray();
        });
    }

    /**
     * Build search filters from parameters.
     */
    private function buildFilters(array $params): array
    {
        $filters = [];

        // Always filter by active status
        $filters[] = 'status = active';

        if (!empty($params['category_id'])) {
            $filters[] = "category_id = {$params['category_id']}";
        }

        if (!empty($params['min_price'])) {
            $filters[] = "price >= {$params['min_price']}";
        }

        if (!empty($params['max_price'])) {
            $filters[] = "price <= {$params['max_price']}";
        }

        if (isset($params['in_stock']) && $params['in_stock']) {
            $filters[] = 'in_stock = true';
        }

        if (isset($params['featured']) && $params['featured']) {
            $filters[] = 'is_featured = true';
        }

        if (!empty($params['brand'])) {
            $filters[] = "brand = '{$params['brand']}'";
        }

        return $filters;
    }

    /**
     * Build sort parameters.
     */
    private function buildSort(string $sort): array
    {
        return match ($sort) {
            'price_low' => ['price:asc'],
            'price_high' => ['price:desc'],
            'name' => ['name:asc'],
            'newest' => ['created_at:desc'],
            'rating' => ['rating:desc', 'created_at:desc'],
            'popularity' => ['popularity_score:desc', 'created_at:desc'],
            'featured' => ['is_featured:desc', 'created_at:desc'],
            default => [], // Meilisearch relevance
        };
    }

    /**
     * Index all products to Meilisearch.
     */
    public function indexAllProducts(): void
    {
        \Log::info('Starting product indexing...');

        Product::chunk(100, function ($products) {
            foreach ($products as $product) {
                $product->searchable();
            }
        });

        \Log::info('Product indexing completed.');
    }

    /**
     * Configure Meilisearch index settings.
     */
    public function configureIndex(): void
    {
        $index = $this->client->index('products');

        // Set searchable attributes
        $index->updateSearchableAttributes([
            'name',
            'description',
            'short_description',
            'tags',
            'brand',
            'sku'
        ]);

        // Set filterable attributes
        $index->updateFilterableAttributes([
            'category_id',
            'price',
            'in_stock',
            'brand',
            'status',
            'seller_id',
            'created_at',
            'is_featured'
        ]);

        // Set sortable attributes
        $index->updateSortableAttributes([
            'price',
            'created_at',
            'popularity_score',
            'rating',
            'name'
        ]);

        // Set ranking rules
        $index->updateRankingRules([
            'words',
            'typo',
            'proximity',
            'attribute',
            'sort',
            'exactness',
            'popularity_score:desc',
            'is_featured:desc'
        ]);

        \Log::info('Index configuration updated.');
    }
}