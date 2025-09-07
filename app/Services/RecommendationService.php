<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\UserProductView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class RecommendationService
{
    protected string $aiApiKey;
    protected string $aiBaseUrl;
    protected string $aiModel;

    public function __construct()
    {
        $this->aiApiKey = config('services_external.ai.openrouter.api_key');
        $this->aiBaseUrl = config('services_external.ai.openrouter.base_url');
        $this->aiModel = config('services_external.ai.openrouter.model');
    }

    /**
     * Get personalized product recommendations for a user.
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 8): Collection
    {
        $cacheKey = "user_recommendations_{$user->id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($user, $limit) {
            $recommendations = collect();

            // 1. Collaborative filtering based on order history
            $orderBasedRecommendations = $this->getOrderBasedRecommendations($user, $limit / 2);
            $recommendations = $recommendations->concat($orderBasedRecommendations);

            // 2. Content-based filtering based on viewed products
            $viewBasedRecommendations = $this->getViewBasedRecommendations($user, $limit / 2);
            $recommendations = $recommendations->concat($viewBasedRecommendations);

            // 3. Fill remaining slots with trending products
            if ($recommendations->count() < $limit) {
                $remaining = $limit - $recommendations->count();
                $trendingProducts = $this->getTrendingProducts($remaining);
                $recommendations = $recommendations->concat($trendingProducts);
            }

            return $recommendations->unique('id')->take($limit);
        });
    }

    /**
     * Get AI-powered product recommendations.
     */
    public function getAIRecommendations(User $user, array $context = []): Collection
    {
        try {
            $prompt = $this->buildRecommendationPrompt($user, $context);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->aiBaseUrl . '/chat/completions', [
                'model' => $this->aiModel,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI product recommendation engine for SmartMart. Provide personalized product recommendations based on user data.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $aiResponse = $response->json();
                $recommendations = $this->parseAIRecommendations($aiResponse['choices'][0]['message']['content'] ?? '');
                return $this->getProductsByAIRecommendations($recommendations);
            }
        } catch (\Exception $e) {
            \Log::error('AI recommendation error: ' . $e->getMessage());
        }

        // Fallback to standard recommendations
        return $this->getPersonalizedRecommendations($user);
    }

    /**
     * Get product recommendations based on a specific product.
     */
    public function getRelatedProducts(Product $product, int $limit = 8): Collection
    {
        $cacheKey = "related_products_{$product->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($product, $limit) {
            $relatedProducts = collect();

            // 1. Same category products
            $categoryProducts = Product::active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inStock()
                ->take($limit / 2)
                ->get();
            $relatedProducts = $relatedProducts->concat($categoryProducts);

            // 2. Products with similar tags
            if ($product->tags) {
                $tagProducts = Product::active()
                    ->where('id', '!=', $product->id)
                    ->where(function ($query) use ($product) {
                        foreach ($product->tags as $tag) {
                            $query->orWhereJsonContains('tags', $tag);
                        }
                    })
                    ->inStock()
                    ->take($limit / 2)
                    ->get();
                $relatedProducts = $relatedProducts->concat($tagProducts);
            }

            // 3. Frequently bought together
            $frequentlyBought = $this->getFrequentlyBoughtTogether($product, $limit / 4);
            $relatedProducts = $relatedProducts->concat($frequentlyBought);

            return $relatedProducts->unique('id')->take($limit);
        });
    }

    /**
     * Get trending products based on recent activity.
     */
    public function getTrendingProducts(int $limit = 12): Collection
    {
        $cacheKey = 'trending_products';
        
        return Cache::remember($cacheKey, 1800, function () use ($limit) {
            $days = config('smartmart.search.trending_days', 7);
            
            // Get products with high recent activity
            return Product::active()
                ->inStock()
                ->withCount([
                    'orderItems as recent_orders' => function ($query) use ($days) {
                        $query->whereHas('order', function ($q) use ($days) {
                            $q->where('created_at', '>=', now()->subDays($days));
                        });
                    },
                    'viewedByUsers as recent_views' => function ($query) use ($days) {
                        $query->where('viewed_at', '>=', now()->subDays($days));
                    }
                ])
                ->orderByRaw('(recent_orders * 3 + recent_views) DESC')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get recommendations based on user's order history.
     */
    private function getOrderBasedRecommendations(User $user, int $limit): Collection
    {
        // Get categories from user's previous orders
        $orderCategories = $user->orders()
            ->with('items.product')
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product.category_id');
            })
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3);

        if ($orderCategories->isEmpty()) {
            return collect();
        }

        // Find users who bought similar products
        $similarUsers = Order::whereHas('items.product', function ($query) use ($orderCategories) {
            $query->whereIn('category_id', $orderCategories);
        })
        ->where('user_id', '!=', $user->id)
        ->where('created_at', '>=', now()->subMonths(3))
        ->select('user_id')
        ->distinct()
        ->pluck('user_id')
        ->take(50);

        // Get products bought by similar users that current user hasn't bought
        $userProductIds = $user->orders()
            ->with('items')
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product_id');
            })
            ->unique();

        return Product::active()
            ->inStock()
            ->whereHas('orderItems.order', function ($query) use ($similarUsers) {
                $query->whereIn('user_id', $similarUsers);
            })
            ->whereNotIn('id', $userProductIds)
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get recommendations based on user's viewed products.
     */
    private function getViewBasedRecommendations(User $user, int $limit): Collection
    {
        $viewedProducts = $user->viewedProducts()
            ->wherePivot('viewed_at', '>=', now()->subWeeks(2))
            ->orderByPivot('viewed_at', 'desc')
            ->take(10)
            ->get();

        if ($viewedProducts->isEmpty()) {
            return collect();
        }

        $viewedCategories = $viewedProducts->pluck('category_id')->unique();
        $viewedTags = $viewedProducts->flatMap(function ($product) {
            return $product->tags ?? [];
        })->unique();

        return Product::active()
            ->inStock()
            ->where(function ($query) use ($viewedCategories, $viewedTags) {
                $query->whereIn('category_id', $viewedCategories);
                
                if ($viewedTags->isNotEmpty()) {
                    $query->orWhere(function ($q) use ($viewedTags) {
                        foreach ($viewedTags as $tag) {
                            $q->orWhereJsonContains('tags', $tag);
                        }
                    });
                }
            })
            ->whereNotIn('id', $viewedProducts->pluck('id'))
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Get frequently bought together products.
     */
    private function getFrequentlyBoughtTogether(Product $product, int $limit): Collection
    {
        // Find orders that contain this product
        $orderIds = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            })
            ->pluck('order_id')
            ->unique();

        if ($orderIds->isEmpty()) {
            return collect();
        }

        // Find products frequently bought with this product
        return Product::active()
            ->inStock()
            ->whereHas('orderItems', function ($query) use ($orderIds, $product) {
                $query->whereIn('order_id', $orderIds)
                    ->where('product_id', '!=', $product->id);
            })
            ->withCount(['orderItems' => function ($query) use ($orderIds) {
                $query->whereIn('order_id', $orderIds);
            }])
            ->orderByDesc('order_items_count')
            ->take($limit)
            ->get();
    }

    /**
     * Build AI recommendation prompt.
     */
    private function buildRecommendationPrompt(User $user, array $context): string
    {
        $userProfile = [
            'user_id' => $user->id,
            'preferences' => $user->preferences ?? [],
            'recent_orders' => $user->orders()->latest()->take(5)->with('items.product:id,name,category_id')->get(),
            'viewed_products' => $user->viewedProducts()->latest('pivot_viewed_at')->take(10)->get(['id', 'name', 'category_id']),
        ];

        $availableProducts = Product::active()->inStock()->take(50)->get(['id', 'name', 'category_id', 'price', 'tags']);

        return "Based on the following user profile and available products, recommend 8 products that would be most relevant to this user:\n\n" .
               "User Profile:\n" . json_encode($userProfile, JSON_PRETTY_PRINT) . "\n\n" .
               "Available Products:\n" . json_encode($availableProducts, JSON_PRETTY_PRINT) . "\n\n" .
               "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n\n" .
               "Please respond with a JSON array of product IDs in order of relevance, explaining briefly why each product was recommended.";
    }

    /**
     * Parse AI response and extract product recommendations.
     */
    private function parseAIRecommendations(string $aiResponse): array
    {
        // Extract JSON from AI response
        if (preg_match('/\[[\d\s,]+\]/', $aiResponse, $matches)) {
            try {
                return json_decode($matches[0], true) ?? [];
            } catch (\Exception $e) {
                // Fallback parsing
                preg_match_all('/\d+/', $aiResponse, $numbers);
                return array_map('intval', array_slice($numbers[0] ?? [], 0, 8));
            }
        }

        return [];
    }

    /**
     * Get products by AI recommendation IDs.
     */
    private function getProductsByAIRecommendations(array $productIds): Collection
    {
        if (empty($productIds)) {
            return collect();
        }

        return Product::active()
            ->inStock()
            ->whereIn('id', $productIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $productIds) . ')')
            ->get();
    }
}