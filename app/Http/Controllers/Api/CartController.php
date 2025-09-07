<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Get user's cart items
     */
    public function index(Request $request)
    {
        $cartItems = Cart::with(['product.media', 'product.variants'])
            ->where('user_id', $request->user()->getKey())
            ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'items' => $cartItems,
            'total' => $total,
            'count' => $cartItems->sum('quantity'),
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'Product is not available',
            ]);
        }

        if ($product->stock_quantity < $request->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available',
            ]);
        }

        $cartItem = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->getKey(),
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
            ],
            [
                'quantity' => \DB::raw('quantity + ' . $request->quantity),
            ]
        );

        return response()->json([
            'message' => 'Item added to cart successfully',
            'item' => $cartItem->load('product'),
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->getKey()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($cart->product->stock_quantity < $request->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available',
            ]);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart item updated successfully',
            'item' => $cart->load('product'),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->getKey()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully',
        ]);
    }
}