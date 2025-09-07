<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Services\RecommendationService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    protected SearchService $searchService;
    protected RecommendationService $recommendationService;

    public function __construct(SearchService $searchService, RecommendationService $recommendationService)
    {
        $this->searchService = $searchService;
        $this->recommendationService = $recommendationService;
    }

    /**
     * Handle product search.
     */
    public function search(Request $request): Response
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'category' => 'nullable|integer|exists:categories,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'sort' => 'nullable|in:relevance,price_low,price_high,name,newest,rating,popularity',
            'page' => 'nullable|integer|min:1',
        ]);

        $params = [
            'q' => $request->q,
            'category_id' => $request->category,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'sort' => $request->sort ?? 'relevance',
            'limit' => 24,
            'offset' => ($request->get('page', 1) - 1) * 24,
        ];

        $results = $this->searchService->searchProducts($params);

        // Track search query
        if (Auth::check()) {
            $this->searchService->trackSearch($request->q, Auth::user());
        }

        // Get search suggestions and trending searches
        $suggestions = $this->searchService->getSuggestions($request->q);
        $trendingSearches = $this->searchService->getTrendingSearches();

        return Inertia::render('Search/Results', [
            'query' => $request->q,
            'results' => $results,
            'suggestions' => $suggestions,
            'trendingSearches' => $trendingSearches,
            'filters' => $request->only(['category', 'min_price', 'max_price', 'sort']),
            'facets' => $results['facets'] ?? [],
        ]);
    }

    /**
     * Get search suggestions via AJAX.
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $suggestions = $this->searchService->getSuggestions($request->q, 8);
        $trendingSearches = $this->searchService->getTrendingSearches(5);

        return response()->json([
            'suggestions' => $suggestions,
            'trending' => $trendingSearches,
        ]);
    }

    /**
     * Get personalized recommendations for user.
     */
    public function recommendations(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $type = $request->get('type', 'personalized');
        $limit = min($request->get('limit', 8), 20);

        $recommendations = match ($type) {
            'ai' => $this->recommendationService->getAIRecommendations(Auth::user()),
            'trending' => $this->recommendationService->getTrendingProducts($limit),
            default => $this->recommendationService->getPersonalizedRecommendations(Auth::user(), $limit),
        };

        return response()->json([
            'recommendations' => $recommendations->load(['category:id,name']),
            'type' => $type,
        ]);
    }

    /**
     * Get related products for a specific product.
     */
    public function relatedProducts(Product $product, Request $request)
    {
        $limit = min($request->get('limit', 8), 20);
        
        $relatedProducts = $this->recommendationService->getRelatedProducts($product, $limit);

        return response()->json([
            'products' => $relatedProducts->load(['category:id,name']),
        ]);
    }

    /**
     * Auto-complete search endpoint.
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:50',
        ]);

        $query = $request->q;
        $limit = 10;

        // Get product name suggestions
        $productSuggestions = Product::active()
            ->where('name', 'like', "%{$query}%")
            ->orderByRaw("CASE WHEN name LIKE '{$query}%' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->take($limit)
            ->pluck('name')
            ->unique()
            ->values();

        // Get category suggestions
        $categorySuggestions = \App\Models\Category::active()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->take(5)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'products' => $productSuggestions,
            'categories' => $categorySuggestions,
        ]);
    }

    /**
     * Get search analytics for admin.
     */
    public function analytics(Request $request)
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $days = $request->get('days', 7);
        
        $analytics = [
            'top_searches' => \DB::table('search_analytics')
                ->select('query', \DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit(20)
                ->get(),
                
            'search_volume' => \DB::table('search_analytics')
                ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
                
            'no_results_queries' => \DB::table('search_analytics')
                ->select('query', \DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays($days))
                ->where('results_count', 0)
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return response()->json($analytics);
    }
}