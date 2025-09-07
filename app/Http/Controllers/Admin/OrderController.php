<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:orders.view')->only(['index', 'show']);
        $this->middleware('permission:orders.update')->only(['update', 'updateStatus', 'markAsShipped']);
        $this->middleware('permission:orders.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $query = Order::with(['user', 'items.product'])
            ->select('orders.*')
            ->when($request->search, function ($q, $search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->payment_status, function ($q, $paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            });

        $orders = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_payments' => Order::where('payment_status', 'pending')->sum('total_amount'),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'payment_status', 'date_from', 'date_to']),
            'order_statuses' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
            ],
            'payment_statuses' => [
                'pending' => 'Pending',
                'paid' => 'Paid',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'user',
            'items.product.images',
            'items.productVariant'
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'timeline' => $this->getOrderTimeline($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'tracking_info' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $order) {
            $oldStatus = $order->status;
            $newStatus = $request->status;

            // Update order status
            $order->update([
                'status' => $newStatus,
                'tracking_info' => $request->tracking_info,
                'notes' => $request->notes,
            ]);

            // Handle status-specific actions
            if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
                $order->markAsShipped($request->tracking_info ?? []);
            } elseif ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                $order->markAsDelivered();
            } elseif ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $order->cancel();
            }

            // Log status change
            activity()
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'notes' => $request->notes,
                ])
                ->log('Order status changed');
        });

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function markAsShipped(Request $request, Order $order)
    {
        $request->validate([
            'carrier' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        if (!$order->canBeShipped()) {
            return redirect()->back()->withErrors(['status' => 'This order cannot be shipped.']);
        }

        $trackingInfo = [
            'carrier' => $request->carrier,
            'tracking_number' => $request->tracking_number,
            'tracking_url' => $request->tracking_url,
            'shipped_by' => auth()->user()->name,
            'shipped_at' => now()->toISOString(),
        ];

        $order->markAsShipped($trackingInfo);

        // TODO: Send shipping notification to customer

        return redirect()->back()->with('success', 'Order marked as shipped successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'action' => 'required|in:mark_processing,mark_shipped,cancel,export',
            'tracking_info' => 'nullable|array',
        ]);

        $orders = Order::whereIn('id', $request->order_ids);

        switch ($request->action) {
            case 'mark_processing':
                $orders->where('status', 'pending')->update(['status' => 'processing']);
                $message = 'Selected orders marked as processing.';
                break;

            case 'mark_shipped':
                $eligibleOrders = $orders->where('status', 'processing')
                    ->where('payment_status', 'paid')
                    ->get();

                foreach ($eligibleOrders as $order) {
                    $order->markAsShipped($request->tracking_info ?? []);
                }
                $message = count($eligibleOrders) . ' orders marked as shipped.';
                break;

            case 'cancel':
                $eligibleOrders = $orders->whereIn('status', ['pending', 'processing'])->get();
                foreach ($eligibleOrders as $order) {
                    $order->cancel();
                }
                $message = count($eligibleOrders) . ' orders cancelled.';
                break;

            case 'export':
                // TODO: Implement order export functionality
                $message = 'Order export initiated.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function analytics(Request $request): Response
    {
        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Revenue analytics
        $revenueData = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = OrderItem::select('product_id', 'products.name', 'products.sku')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.quantity) as total_sold, SUM(order_items.total_price) as revenue')
            ->groupBy('product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Order status distribution
        $statusDistribution = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Payment method analytics
        $paymentMethods = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('payment_method', DB::raw('COUNT(*) as count, SUM(total_amount) as revenue'))
            ->groupBy('payment_method')
            ->get();

        return Inertia::render('Admin/Orders/Analytics', [
            'revenue_data' => $revenueData,
            'top_products' => $topProducts,
            'status_distribution' => $statusDistribution,
            'payment_methods' => $paymentMethods,
            'filters' => compact('dateFrom', 'dateTo'),
            'summary' => [
                'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_revenue' => Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('total_amount'),
                'average_order_value' => Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->avg('total_amount'),
                'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
            ],
        ]);
    }

    private function getOrderTimeline(Order $order): array
    {
        $timeline = [];

        $timeline[] = [
            'event' => 'Order Created',
            'date' => $order->created_at,
            'status' => 'completed',
        ];

        if ($order->payment_status === 'paid') {
            $timeline[] = [
                'event' => 'Payment Confirmed',
                'date' => $order->created_at, // Assuming payment happens immediately
                'status' => 'completed',
            ];
        }

        if ($order->status === 'processing') {
            $timeline[] = [
                'event' => 'Order Processing',
                'date' => $order->updated_at,
                'status' => 'completed',
            ];
        }

        if ($order->shipped_at) {
            $timeline[] = [
                'event' => 'Order Shipped',
                'date' => $order->shipped_at,
                'status' => 'completed',
                'tracking' => $order->tracking_info,
            ];
        }

        if ($order->delivered_at) {
            $timeline[] = [
                'event' => 'Order Delivered',
                'date' => $order->delivered_at,
                'status' => 'completed',
            ];
        }

        if ($order->status === 'cancelled') {
            $timeline[] = [
                'event' => 'Order Cancelled',
                'date' => $order->updated_at,
                'status' => 'cancelled',
            ];
        }

        return $timeline;
    }

    private function calculateConversionRate(string $dateFrom, string $dateTo): float
    {
        // This is a simplified calculation
        // In real implementation, you'd track cart abandonment and actual visits
        $totalOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $paidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        return $totalOrders > 0 ? ($paidOrders / $totalOrders) * 100 : 0;
    }
}