<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user's orders.
     */
    public function index(Request $request): Response
    {
        $query = Order::where('user_id', Auth::id())
            ->with(['items.product:id,name,slug,images'])
            ->latest();

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(10)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
            'statuses' => config('smartmart.orders.statuses'),
        ]);
    }

    /**
     * Display specific order.
     */
    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'items.product:id,name,slug,images',
            'items.productVariant:id,name,options'
        ]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        $this->authorize('update', $order);

        if (!$order->canBeCancelled()) {
            return back()->withErrors(['order' => 'This order cannot be cancelled.']);
        }

        $order->cancel();

        return back()->with('success', 'Order cancelled successfully!');
    }

    /**
     * Request a refund for an order.
     */
    public function requestRefund(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$order->canBeRefunded()) {
            return back()->withErrors(['order' => 'This order is not eligible for refund.']);
        }

        // Create refund request (you would implement RefundRequest model)
        // RefundRequest::create([
        //     'order_id' => $order->id,
        //     'reason' => $request->reason,
        //     'status' => 'pending',
        // ]);

        return back()->with('success', 'Refund request submitted successfully!');
    }

    /**
     * Reorder items from a previous order.
     */
    public function reorder(Order $order)
    {
        $this->authorize('view', $order);

        $cart = $this->getUserCart();
        $addedItems = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $variant = $item->productVariant;

            // Check if product is still available
            if (!$product || !$product->canPurchase()) {
                continue;
            }

            // Check stock
            $availableStock = $variant ? $variant->quantity : $product->quantity;
            if ($product->track_quantity && $availableStock < $item->quantity) {
                continue;
            }

            // Add to cart
            $cart->items()->updateOrCreate([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
            ], [
                'quantity' => $item->quantity,
                'unit_price' => $variant ? $variant->price : $product->price,
                'total_price' => $item->quantity * ($variant ? $variant->price : $product->price),
            ]);

            $addedItems++;
        }

        $cart->updateTotals();

        if ($addedItems > 0) {
            return redirect()->route('cart.index')
                ->with('success', "{$addedItems} items added to your cart!");
        } else {
            return back()->with('error', 'No items from this order could be added to your cart.');
        }
    }

    /**
     * Download order invoice (PDF).
     */
    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);

        // Generate PDF invoice using DomPDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.order', compact('order'));

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    /**
     * Track order shipment.
     */
    public function track(Order $order): Response
    {
        $this->authorize('view', $order);

        $trackingInfo = $order->tracking_info;
        $trackingEvents = [];

        // If tracking info exists, fetch tracking events from shipping provider
        if ($trackingInfo && isset($trackingInfo['tracking_number'])) {
            // Here you would integrate with shipping provider APIs
            // $trackingEvents = $this->getTrackingEvents($trackingInfo);
        }

        return Inertia::render('Orders/Track', [
            'order' => $order,
            'trackingEvents' => $trackingEvents,
        ]);
    }

    /**
     * Get user's cart.
     */
    private function getUserCart()
    {
        return \App\Models\Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ], [
            'total_amount' => 0,
            'item_count' => 0,
        ]);
    }
}