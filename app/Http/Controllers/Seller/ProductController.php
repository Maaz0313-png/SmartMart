<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:seller']);
    }

    /**
     * Display a listing of seller's products.
     */
    public function index(Request $request): Response
    {
        $query = Product::where('user_id', Auth::id())
            ->with(['category', 'variants'])
            ->withCount('reviews')
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(15)->withQueryString();

        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): Response
    {
        return Inertia::render('Seller/Products/Create', [
            'categories' => Category::active()->with('children')->get(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0|gt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'track_quantity' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,lb,g,oz',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'seo_data' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.quantity' => 'required_with:variants|integer|min:0',
            'variants.*.options' => 'nullable|array',
        ]);

        // Generate SKU and slug
        $validated['sku'] = $this->generateSku($validated['name']);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['user_id'] = Auth::id();

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

        // Create variants if provided
        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $variantData['product_id'] = $product->id;
                $variantData['sku'] = $this->generateSku($product->name . ' ' . $variantData['name']);
                ProductVariant::create($variantData);
            }
        }

        return redirect()->route('seller.products.show', $product)
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): Response
    {
        $this->authorize('view', $product);

        $product->load(['category', 'variants', 'reviews.user']);

        return Inertia::render('Seller/Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load(['variants']);

        return Inertia::render('Seller/Products/Edit', [
            'product' => $product,
            'categories' => Category::active()->with('children')->get(),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
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
            'meta_data' => 'nullable|array',
            'existing_images' => 'nullable|array',
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

        return redirect()->route('seller.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Delete product images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk actions for products.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $products = Product::where('user_id', Auth::id())
            ->whereIn('id', $request->product_ids);

        switch ($request->action) {
            case 'activate':
                $products->update(['status' => 'active', 'published_at' => now()]);
                $message = 'Products activated successfully!';
                break;
            case 'deactivate':
                $products->update(['status' => 'inactive', 'published_at' => null]);
                $message = 'Products deactivated successfully!';
                break;
            case 'delete':
                $productList = $products->get();
                foreach ($productList as $product) {
                    $this->authorize('delete', $product);
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
     * Generate unique SKU for product.
     */
    private function generateSku(string $name): string
    {
        $base = strtoupper(Str::slug($name, ''));
        $base = substr($base, 0, 6);
        
        $counter = 1;
        $sku = $base . sprintf('%04d', $counter);
        
        while (Product::where('sku', $sku)->exists() || ProductVariant::where('sku', $sku)->exists()) {
            $counter++;
            $sku = $base . sprintf('%04d', $counter);
        }
        
        return $sku;
    }
}