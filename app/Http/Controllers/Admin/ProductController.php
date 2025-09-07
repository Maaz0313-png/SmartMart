<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display products list.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['category:id,name', 'seller:id,name'])
            ->withCount('orderItems')
            ->latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $lowStockThreshold = config('smartmart.shop.low_stock_threshold', 5);
                    $query->whereRaw('quantity <= ? AND quantity > 0', [$lowStockThreshold]);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
            }
        }

        if ($request->filled('seller')) {
            $query->where('user_id', $request->seller);
        }

        $products = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::active()->get(['id', 'name']),
            'sellers' => User::role('seller')->get(['id', 'name']),
            'filters' => $request->only(['search', 'category', 'status', 'stock_status', 'seller']),
            'stats' => [
                'total' => Product::count(),
                'active' => Product::active()->count(),
                'low_stock' => Product::whereRaw('quantity <= ? AND quantity > 0', [config('smartmart.shop.low_stock_threshold', 5)])->count(),
                'out_of_stock' => Product::where('quantity', 0)->count(),
            ],
        ]);
    }

    /**
     * Show product details.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'category',
            'seller',
            'variants',
            'reviews.user',
            'orderItems.order:id,order_number,created_at',
        ]);

        $productStats = [
            'total_sold' => $product->orderItems()->sum('quantity'),
            'revenue_generated' => $product->orderItems()
                ->whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
                ->sum('total_price'),
            'reviews_count' => $product->reviews()->count(),
            'average_rating' => $product->reviews()->avg('rating') ?? 0,
            'views_count' => $product->viewedByUsers()->sum('view_count'),
        ];

        return Inertia::render('Admin/Products/Show', [
            'product' => $product,
            'productStats' => $productStats,
        ]);
    }

    /**
     * Show create product form.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::active()->with('children')->get(),
            'sellers' => User::role('seller')->get(['id', 'name']),
        ]);
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0|gt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'track_quantity' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,lb,g,oz',
            'dimensions' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'seo_data' => 'nullable|array',
        ]);

        // Generate SKU and slug
        $validated['sku'] = $this->generateSku($validated['name']);
        $validated['slug'] = Str::slug($validated['name']);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Set published_at if status is active
        if ($validated['status'] === 'active') {
            $validated['published_at'] = now();
        }

        $product = Product::create($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product created successfully!');
    }

    /**
     * Show edit product form.
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->load('variants'),
            'categories' => Category::active()->with('children')->get(),
            'sellers' => User::role('seller')->get(['id', 'name']),
        ]);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0|gt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'track_quantity' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,lb,g,oz',
            'dimensions' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'existing_images' => 'nullable|array',
            'seo_data' => 'nullable|array',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $product->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle image uploads
        $existingImages = $request->get('existing_images', []);
        $newImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $newImages[] = $path;
            }
        }

        // Delete removed images
        if ($product->images) {
            $imagesToDelete = array_diff($product->images, $existingImages);
            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $validated['images'] = array_merge($existingImages, $newImages);

        // Set published_at if status changed to active
        if ($validated['status'] === 'active' && $product->status !== 'active') {
            $validated['published_at'] = now();
        } elseif ($validated['status'] !== 'active') {
            $validated['published_at'] = null;
        }

        $product->update($validated);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product)
    {
        // Check if product has orders
        if ($product->orderItems()->count() > 0) {
            return back()->withErrors(['product' => 'Cannot delete product that has been ordered.']);
        }

        // Delete product images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk actions for products.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->product_ids);

        switch ($request->action) {
            case 'activate':
                $products->update(['status' => 'active', 'published_at' => now()]);
                $message = 'Products activated successfully!';
                break;
            case 'deactivate':
                $products->update(['status' => 'inactive', 'published_at' => null]);
                $message = 'Products deactivated successfully!';
                break;
            case 'feature':
                $products->update(['is_featured' => true]);
                $message = 'Products featured successfully!';
                break;
            case 'unfeature':
                $products->update(['is_featured' => false]);
                $message = 'Products unfeatured successfully!';
                break;
            case 'delete':
                // Check for products with orders
                $productsWithOrders = $products->whereHas('orderItems')->pluck('id')->toArray();
                if (!empty($productsWithOrders)) {
                    return back()->withErrors(['products' => 'Some products cannot be deleted as they have been ordered.']);
                }
                
                $productList = $products->get();
                foreach ($productList as $product) {
                    if ($product->images) {
                        foreach ($product->images as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
                $products->delete();
                $message = 'Products deleted successfully!';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Generate unique SKU.
     */
    private function generateSku(string $name): string
    {
        $base = strtoupper(Str::slug($name, ''));
        $base = substr($base, 0, 6);
        
        $counter = 1;
        $sku = $base . sprintf('%04d', $counter);
        
        while (Product::where('sku', $sku)->exists()) {
            $counter++;
            $sku = $base . sprintf('%04d', $counter);
        }
        
        return $sku;
    }
}