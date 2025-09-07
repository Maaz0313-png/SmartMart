<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order
     */
    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // Validate stock availability
            $this->validateStock($data['items']);

            // Create order
            $order = Order::create([
                'user_id' => $user->getKey(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'],
                'shipping_address' => $data['shipping_address'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => 0,
            ]);

            $subtotal = 0;

            // Create order items
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for product: {$product->name}"
                    ]);
                }

                $price = $product->price;
                $itemTotal = $price * $item['quantity'];
                $subtotal += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'total' => $itemTotal,
                ]);

                // Reduce stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Calculate totals
            $taxRate = config('smartmart.tax.rate', 0.1);
            $taxAmount = $subtotal * $taxRate;
            $shippingAmount = $this->calculateShipping($subtotal);
            $totalAmount = $subtotal + $taxAmount + $shippingAmount;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
            ]);

            // Clear user's cart
            $user->cart()->delete();

            return $order;
        });
    }

    /**
     * Validate stock availability for all items
     */
    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => "Product not found: {$item['product_id']}"
                ]);
            }

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "Product is not active: {$product->name}"
                ]);
            }

            if ($product->stock_quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}"
                ]);
            }
        }
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShipping(float $subtotal): float
    {
        $freeShippingThreshold = config('smartmart.shipping.free_threshold', 100);
        $standardShipping = config('smartmart.shipping.standard_rate', 10);

        return $subtotal >= $freeShippingThreshold ? 0 : $standardShipping;
    }
}