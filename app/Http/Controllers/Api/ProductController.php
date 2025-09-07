<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'category' => 'nullable|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'sort' => 'nullable|in:price_low,price_high,name,newest,rating,featured',
            'featured' => 'nullable|boolean',
            'in_stock' => 'nullable|boolean',
        ]);

        $query = Product::with(['category:id,name,slug', 'seller:id,name'])
            ->select(['id', 'name', 'slug', 'price', 'compare_price', 'images', 'category_id', 'user_id', 'is_featured', 'quantity', 'track_quantity'])
            ->active();

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Apply sorting
        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_low' => $query->orderBy('price', 'asc'),
                'price_high' => $query->orderBy('price', 'desc'),
                'name' => $query->orderBy('name', 'asc'),
                'newest' => $query->latest(),
                'rating' => $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc'),
                'featured' => $query->orderByDesc('is_featured')->latest(),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'category:id,name,slug',
            'seller:id,name',
            'variants' => fn($query) => $query->where('is_active', true),
            'reviews' => fn($query) => $query->with('user:id,name')->latest()->take(10),
        ]);

        // Record product view for authenticated users
        if (auth('sanctum')->check()) {
            $product->recordView(auth('sanctum')->user());
        }

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Search products using Scout.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'category' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Product::search($request->q)
            ->where('status', 'active');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->take($request->get('limit', 20))
            ->get()
            ->load(['category:id,name', 'seller:id,name']);

        return response()->json([
            'data' => $products,
            'meta' => [
                'query' => $request->q,
                'total' => $products->count(),
            ],
        ]);
    }

    /**
     * Get featured products.
     */
    public function featured()
    {
        $products = Product::with(['category:id,name', 'seller:id,name'])
            ->select(['id', 'name', 'slug', 'price', 'compare_price', 'images', 'category_id', 'user_id'])
            ->active()
            ->featured()
            ->inStock()
            ->take(12)
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Get product recommendations.
     */
    public function recommendations(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = Product::with(['category:id,name', 'seller:id,name'])
            ->select(['id', 'name', 'slug', 'price', 'compare_price', 'images', 'category_id', 'user_id'])
            ->active()
            ->inStock();

        if ($request->filled('product_id')) {
            $product = Product::findOrFail($request->product_id);
            $query->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id);
        } elseif ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        $products = $query->take($request->get('limit', 8))->get();

        return response()->json([
            'data' => $products,
        ]);
    }
}