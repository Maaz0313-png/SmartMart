<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'payments'])
            ->where('user_id', $request->user()->getKey())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Get specific order
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->getKey()) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->load(['items.product.media', 'payments', 'shippingAddress']);

        return response()->json($order);
    }

    /**
     * Create new order
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,razorpay',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.address_line_1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
        ]);

        try {
            $order = $this->orderService->createOrder($request->user(), [
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'items' => $request->items,
            ]);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load(['items.product', 'payments']),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}