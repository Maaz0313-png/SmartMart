<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the welcome page.
     */
    public function index(): Response
    {
        $featuredProducts = Product::active()
            ->featured()
            ->with(['category', 'reviews'])
            ->take(8)
            ->get();

        $categories = Category::active()
            ->rootCategories()
            ->withCount('products')
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $trendingProducts = Product::active()
            ->with(['category', 'reviews'])
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(8)
            ->get();

        return Inertia::render('Welcome', [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'trendingProducts' => $trendingProducts,
        ]);
    }
}