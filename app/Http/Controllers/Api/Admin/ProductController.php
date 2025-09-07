<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin
     */
    public function index(Request $request)
    {
        $query = Product::with(['user', 'category', 'media']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('user_id', $request->seller_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(20);

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        return new ProductResource(
            $product->load(['user', 'category', 'media', 'variants', 'reviews.user'])
        );
    }

    /**
     * Update the specified product (admin approval/rejection)
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'sometimes|required|in:pending,approved,rejected,inactive',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        $product->update($request->only(['status', 'rejection_reason', 'is_active', 'is_featured']));

        $message = match($request->status) {
            'approved' => 'Product approved successfully',
            'rejected' => 'Product rejected successfully',
            'inactive' => 'Product deactivated successfully',
            default => 'Product updated successfully'
        };

        return response()->json([
            'message' => $message,
            'product' => new ProductResource($product->load(['user', 'category'])),
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Check if product has active orders
        if ($product->orderItems()->whereHas('order', function ($query) {
            $query->whereIn('status', ['pending', 'processing', 'shipped']);
        })->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with active orders'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}