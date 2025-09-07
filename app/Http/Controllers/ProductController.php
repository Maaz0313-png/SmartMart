<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'seller', 'reviews'])
            ->active()
            ->latest();

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

        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_low' => $query->orderBy('price', 'asc'),
                'price_high' => $query->orderBy('price', 'desc'),
                'name' => $query->orderBy('name', 'asc'),
                'newest' => $query->latest(),
                'rating' => $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc'),
                default => $query->latest(),
            };
        }

        $products = $query->paginate(24)->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::active()->rootCategories()->with('children')->get(),
            'filters' => $request->only(['category', 'search', 'min_price', 'max_price', 'sort']),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'category',
            'seller',
            'variants' => fn($query) => $query->where('is_active', true),
            'reviews' => fn($query) => $query->with('user')->latest()->take(10),
        ]);

        // Record product view
        $product->recordView(auth()->user(), session()->getId());

        // Get related products
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(8)
            ->get();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
            'category' => 'nullable|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Product::search($request->query)
            ->where('status', 'active');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->take($request->get('limit', 20))->get();

        return response()->json([
            'products' => $products->load(['category', 'seller']),
            'total' => $products->count(),
        ]);
    }
}