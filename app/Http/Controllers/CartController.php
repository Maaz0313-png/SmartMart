<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the cart.
     */
    public function index(): Response
    {
        $cart = $this->getUserCart();
        
        $cart->load([
            'items.product:id,name,slug,images,status',
            'items.productVariant:id,name,price,quantity,options'
        ]);

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($request->product_id);
        $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;

        // Check if product is available
        if (!$product->canPurchase()) {
            return back()->withErrors(['product' => 'This product is not available for purchase.']);
        }

        // Check stock availability
        $availableStock = $variant ? $variant->quantity : $product->quantity;
        if ($product->track_quantity && $availableStock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Not enough stock available.']);
        }

        $cart = $this->getUserCart();
        $unitPrice = $variant ? $variant->price : $product->price;

        // Check if item already exists in cart
        $existingItem = $cart->items()
            ->where('product_id', $request->product_id)
            ->where('product_variant_id', $request->variant_id)
            ->first();

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem->quantity + $request->quantity;
            
            // Check stock for new quantity
            if ($product->track_quantity && $availableStock < $newQuantity) {
                return back()->withErrors(['quantity' => 'Not enough stock available for requested quantity.']);
            }

            $existingItem->update([
                'quantity' => $newQuantity,
                'total_price' => $newQuantity * $unitPrice,
            ]);
        } else {
            // Create new cart item
            $cart->items()->create([
                'product_id' => $request->product_id,
                'product_variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
                'total_price' => $request->quantity * $unitPrice,
            ]);
        }

        $cart->updateTotals();

        return back()->with('success', 'Item added to cart successfully!');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        // Verify cart item belongs to user's cart
        $cart = $this->getUserCart();
        if ($cartItem->cart_id !== $cart->id) {
            abort(403);
        }

        $product = $cartItem->product;
        $variant = $cartItem->productVariant;

        // Check stock availability
        $availableStock = $variant ? $variant->quantity : $product->quantity;
        if ($product->track_quantity && $availableStock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Not enough stock available.']);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
            'total_price' => $request->quantity * $cartItem->unit_price,
        ]);

        $cart->updateTotals();

        return back()->with('success', 'Cart updated successfully!');
    }

    /**
     * Remove item from cart.
     */
    public function destroy(CartItem $cartItem)
    {
        // Verify cart item belongs to user's cart
        $cart = $this->getUserCart();
        if ($cartItem->cart_id !== $cart->id) {
            abort(403);
        }

        $cartItem->delete();
        $cart->updateTotals();

        return back()->with('success', 'Item removed from cart successfully!');
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        $cart = $this->getUserCart();
        $cart->items()->delete();
        $cart->updateTotals();

        return back()->with('success', 'Cart cleared successfully!');
    }

    /**
     * Get cart count for header display.
     */
    public function count()
    {
        $cart = $this->getUserCart();
        
        return response()->json([
            'count' => $cart->item_count,
            'total' => $cart->formatted_total_amount,
        ]);
    }

    /**
     * Apply coupon code to cart.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        // TODO: Implement coupon validation and discount application
        // This would involve creating a Coupon model and validation logic

        return back()->with('success', 'Coupon applied successfully!');
    }

    /**
     * Get or create user's cart.
     */
    private function getUserCart(): Cart
    {
        if (Auth::check()) {
            // For authenticated users
            $cart = Cart::where('user_id', Auth::id())->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'total_amount' => 0,
                    'item_count' => 0,
                ]);
            }

            // Merge session cart if exists
            $this->mergeSessionCart($cart);
            
            return $cart;
        } else {
            // For guest users
            $sessionId = session()->getId();
            $cart = Cart::where('session_id', $sessionId)->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'session_id' => $sessionId,
                    'total_amount' => 0,
                    'item_count' => 0,
                ]);
            }
            
            return $cart;
        }
    }

    /**
     * Merge session cart with user cart on login.
     */
    private function mergeSessionCart(Cart $userCart): void
    {
        $sessionId = session()->getId();
        $sessionCart = Cart::where('session_id', $sessionId)->first();
        
        if ($sessionCart && $sessionCart->items()->count() > 0) {
            foreach ($sessionCart->items as $sessionItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $sessionItem->product_id)
                    ->where('product_variant_id', $sessionItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    // Merge quantities
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $sessionItem->quantity,
                        'total_price' => ($existingItem->quantity + $sessionItem->quantity) * $existingItem->unit_price,
                    ]);
                } else {
                    // Move item to user cart
                    $sessionItem->update(['cart_id' => $userCart->id]);
                }
            }

            // Delete session cart
            $sessionCart->delete();
            $userCart->updateTotals();
        }
    }
}