<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'specifications' => 'nullable|array',
            'tags' => 'nullable|array',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
        ]);

        $user = $request->user();

        if (!$user->hasRole('seller')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $productData = $request->only([
            'name', 'description', 'price', 'category_id', 
            'stock_quantity', 'sku', 'specifications', 
            'tags', 'weight', 'dimensions'
        ]);

        $productData['user_id'] = $user->getKey();
        $productData['status'] = 'pending'; // Requires admin approval

        $product = Product::create($productData);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->media()->create([
                    'file_path' => $path,
                    'file_type' => 'image',
                    'sort_order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully and is pending approval',
            'product' => new ProductResource($product->load(['media', 'category'])),
        ], 201);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->getKey()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0.01',
            'category_id' => 'sometimes|required|exists:categories,id',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'sku' => 'sometimes|nullable|string|unique:products,sku,' . $product->getKey(),
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'specifications' => 'nullable|array',
            'tags' => 'nullable|array',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($request->only([
            'name', 'description', 'price', 'category_id', 
            'stock_quantity', 'sku', 'specifications', 
            'tags', 'weight', 'dimensions', 'is_active'
        ]));

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->media()->create([
                    'file_path' => $path,
                    'file_type' => 'image',
                    'sort_order' => $product->media()->count() + $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => new ProductResource($product->load(['media', 'category'])),
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->getKey()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if product has active orders
        if ($product->orderItems()->whereHas('order', function ($query) {
            $query->whereIn('status', ['pending', 'processing', 'shipped']);
        })->exists()) {
            throw ValidationException::withMessages([
                'product' => 'Cannot delete product with active orders',
            ]);
        }

        // Delete associated media files
        foreach ($product->media as $media) {
            Storage::disk('public')->delete($media->file_path);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}