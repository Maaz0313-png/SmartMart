<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\StripeClient;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class CheckoutController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->middleware('auth');
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Show checkout page.
     */
    public function index(): Response
    {
        $cart = $this->getUserCart();
        
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $cart->load([
            'items.product:id,name,slug,images,weight,is_digital',
            'items.productVariant:id,name,options'
        ]);

        // Calculate shipping options
        $shippingOptions = $this->calculateShippingOptions($cart);
        
        // Create payment intent for Stripe
        $paymentIntent = $this->createPaymentIntent($cart);

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'shippingOptions' => $shippingOptions,
            'paymentIntent' => $paymentIntent,
            'paymentMethods' => $this->getAvailablePaymentMethods(),
        ]);
    }

    /**
     * Process checkout.
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,cod',
            'billing_address' => 'required|array',
            'billing_address.first_name' => 'required|string|max:255',
            'billing_address.last_name' => 'required|string|max:255',
            'billing_address.email' => 'required|email',
            'billing_address.phone' => 'required|string|max:20',
            'billing_address.address_line_1' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.state' => 'required|string|max:100',
            'billing_address.postal_code' => 'required|string|max:20',
            'billing_address.country' => 'required|string|max:2',
            'shipping_address' => 'required|array',
            'shipping_method' => 'required|string',
            'payment_method_id' => 'required_if:payment_method,stripe|string',
            'same_as_billing' => 'boolean',
        ]);

        $cart = $this->getUserCart();
        
        if ($cart->isEmpty()) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        // Validate stock availability
        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->productVariant;
            $availableStock = $variant ? $variant->quantity : $product->quantity;

            if ($product->track_quantity && $availableStock < $item->quantity) {
                return back()->withErrors(['stock' => "Insufficient stock for {$product->name}."]);
            }
        }

        return DB::transaction(function () use ($request, $cart) {
            // Create order
            $order = $this->createOrder($request, $cart);

            // Process payment
            $paymentResult = $this->processPayment($request, $order);

            if ($paymentResult['success']) {
                // Update order with payment info
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentResult['transaction_id'],
                    'status' => 'processing',
                ]);

                // Reduce stock
                $this->reduceStock($order);

                // Clear cart
                $cart->items()->delete();
                $cart->updateTotals();

                return redirect()->route('orders.confirmation', $order)
                    ->with('success', 'Order placed successfully!');
            } else {
                // Delete order on payment failure
                $order->delete();
                
                return back()->withErrors(['payment' => $paymentResult['message']]);
            }
        });
    }

    /**
     * Show order confirmation page.
     */
    public function confirmation(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'items.productVariant']);

        return Inertia::render('Checkout/Confirmation', [
            'order' => $order,
        ]);
    }

    /**
     * Create order from cart.
     */
    private function createOrder(Request $request, Cart $cart): Order
    {
        $shippingAddress = $request->same_as_billing 
            ? $request->billing_address 
            : $request->shipping_address;

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => Auth::id(),
            'status' => 'pending',
            'currency' => config('smartmart.shop.currency', 'USD'),
            'payment_method' => $request->payment_method,
            'billing_address' => $request->billing_address,
            'shipping_address' => $shippingAddress,
            'coupon_code' => $request->coupon_code,
            'notes' => $request->notes,
        ]);

        // Create order items
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            $variant = $cartItem->productVariant;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_variant_id' => $cartItem->product_variant_id,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'total_price' => $cartItem->total_price,
                'product_snapshot' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'images' => $product->images,
                    'variant_name' => $variant?->name,
                    'variant_options' => $variant?->options,
                ],
            ]);
        }

        // Calculate totals
        $order->calculateTotals();
        
        // Add shipping cost
        $shippingCost = $this->calculateShippingCost($request->shipping_method, $order);
        $order->update(['shipping_amount' => $shippingCost]);
        $order->calculateTotals();

        return $order;
    }

    /**
     * Process payment based on selected method.
     */
    private function processPayment(Request $request, Order $order): array
    {
        switch ($request->payment_method) {
            case 'stripe':
                return $this->processStripePayment($request, $order);
            case 'paypal':
                return $this->processPayPalPayment($request, $order);
            case 'cod':
                return $this->processCodPayment($order);
            default:
                return ['success' => false, 'message' => 'Invalid payment method'];
        }
    }

    /**
     * Process Stripe payment.
     */
    private function processStripePayment(Request $request, Order $order): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->confirm($request->payment_intent_id, [
                'payment_method' => $request->payment_method_id,
                'return_url' => route('checkout.confirmation', $order),
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'transaction_id' => $paymentIntent->id,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Payment failed. Please try again.',
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process PayPal payment.
     */
    private function processPayPalPayment(Request $request, Order $order): array
    {
        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            // Create PayPal order
            $paypalOrder = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $order->currency,
                            'value' => number_format($order->total_amount, 2, '.', ''),
                        ],
                        'description' => 'Order #' . $order->order_number,
                    ],
                ],
            ]);

            // Capture payment
            $result = $provider->capturePaymentOrder($paypalOrder['id']);

            if ($result['status'] === 'COMPLETED') {
                return [
                    'success' => true,
                    'transaction_id' => $result['id'],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'PayPal payment failed.',
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'PayPal processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process Cash on Delivery payment.
     */
    private function processCodPayment(Order $order): array
    {
        // Check if COD is enabled and order amount is within limits
        if (!config('payments.gateways.cod.enabled')) {
            return ['success' => false, 'message' => 'Cash on Delivery is not available.'];
        }

        $maxAmount = config('payments.gateways.cod.max_amount', 1000);
        if ($order->total_amount > $maxAmount) {
            return [
                'success' => false, 
                'message' => "Cash on Delivery is not available for orders over $maxAmount."
            ];
        }

        return [
            'success' => true,
            'transaction_id' => 'cod_' . $order->order_number,
        ];
    }

    /**
     * Reduce stock for order items.
     */
    private function reduceStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            $variant = $item->productVariant;

            if ($variant) {
                $variant->decrement('quantity', $item->quantity);
            } elseif ($product->track_quantity) {
                $product->decrementStock($item->quantity);
            }
        }
    }

    /**
     * Get user's cart.
     */
    private function getUserCart(): Cart
    {
        return Cart::where('user_id', Auth::id())->first() ?? new Cart(['item_count' => 0]);
    }

    /**
     * Calculate shipping options.
     */
    private function calculateShippingOptions(Cart $cart): array
    {
        $options = [
            [
                'id' => 'standard',
                'name' => 'Standard Shipping',
                'description' => '5-7 business days',
                'price' => 9.99,
                'estimated_days' => '5-7',
            ],
            [
                'id' => 'express',
                'name' => 'Express Shipping',
                'description' => '2-3 business days',
                'price' => 19.99,
                'estimated_days' => '2-3',
            ],
        ];

        // Free shipping for orders over threshold
        $freeShippingThreshold = config('smartmart.shop.free_shipping_threshold', 100);
        if ($cart->total_amount >= $freeShippingThreshold) {
            $options[0]['price'] = 0;
            $options[0]['name'] = 'Free Standard Shipping';
        }

        return $options;
    }

    /**
     * Calculate shipping cost.
     */
    private function calculateShippingCost(string $method, Order $order): float
    {
        $shippingOptions = $this->calculateShippingOptions(new Cart(['total_amount' => $order->subtotal]));
        
        foreach ($shippingOptions as $option) {
            if ($option['id'] === $method) {
                return $option['price'];
            }
        }

        return 0;
    }

    /**
     * Create Stripe payment intent.
     */
    private function createPaymentIntent(Cart $cart): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => round($cart->total_amount * 100), // Convert to cents
                'currency' => strtolower(config('smartmart.shop.currency', 'USD')),
                'metadata' => [
                    'cart_id' => $cart->id,
                    'user_id' => Auth::id(),
                ],
            ]);

            return [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get available payment methods.
     */
    private function getAvailablePaymentMethods(): array
    {
        $methods = [];

        if (config('payments.gateways.stripe.enabled')) {
            $methods[] = [
                'id' => 'stripe',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay securely with your credit or debit card',
                'icon' => 'credit-card',
            ];
        }

        if (config('payments.gateways.paypal.enabled')) {
            $methods[] = [
                'id' => 'paypal',
                'name' => 'PayPal',
                'description' => 'Pay with your PayPal account',
                'icon' => 'paypal',
            ];
        }

        if (config('payments.gateways.cod.enabled')) {
            $methods[] = [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive your order',
                'icon' => 'cash',
            ];
        }

        return $methods;
    }
}