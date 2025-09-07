<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:analytics.view');
    }

    public function overview(Request $request): Response
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();
        
        // Compare with previous period
        $previousDateFrom = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo) + 1);
        $previousDateTo = $dateFrom->copy()->subDay();

        $currentMetrics = $this->getMetrics($dateFrom, $dateTo);
        $previousMetrics = $this->getMetrics($previousDateFrom, $previousDateTo);

        $comparison = $this->calculateComparison($currentMetrics, $previousMetrics);

        // Revenue trend data
        $revenueTrend = $this->getRevenueTrend($dateFrom, $dateTo);
        
        // Top selling products
        $topProducts = $this->getTopProducts($dateFrom, $dateTo, 10);
        
        // Sales by category
        $salesByCategory = $this->getSalesByCategory($dateFrom, $dateTo);
        
        // Customer acquisition
        $customerAcquisition = $this->getCustomerAcquisition($dateFrom, $dateTo);

        return Inertia::render('Admin/Analytics/Overview', [
            'current_metrics' => $currentMetrics,
            'comparison' => $comparison,
            'revenue_trend' => $revenueTrend,
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
            'customer_acquisition' => $customerAcquisition,
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    public function sales(Request $request): Response
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        // Daily sales data
        $dailySales = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales by payment method
        $salesByPayment = Order::select('payment_method')
            ->selectRaw('COUNT(*) as orders, SUM(total_amount) as revenue')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('payment_method')
            ->get();

        // Order status distribution
        $orderStatusDistribution = Order::select('status')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // Average order value trend
        $avgOrderValue = Order::selectRaw('DATE(created_at) as date, AVG(total_amount) as avg_order_value')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Hourly sales pattern
        $hourlySales = Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return Inertia::render('Admin/Analytics/Sales', [
            'daily_sales' => $dailySales,
            'sales_by_payment' => $salesByPayment,
            'order_status_distribution' => $orderStatusDistribution,
            'avg_order_value' => $avgOrderValue,
            'hourly_sales' => $hourlySales,
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    public function products(Request $request): Response
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        // Top selling products by quantity
        $topSellingByQty = OrderItem::select('product_id', 'products.name', 'products.sku')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.quantity) as total_quantity_sold')
            ->groupBy('product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity_sold')
            ->limit(20)
            ->get();

        // Top selling products by revenue
        $topSellingByRevenue = OrderItem::select('product_id', 'products.name', 'products.sku')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.total_price) as total_revenue')
            ->groupBy('product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('track_quantity', true)
            ->whereRaw('quantity <= min_quantity')
            ->with('category')
            ->get();

        // Product performance over time
        $productPerformance = OrderItem::selectRaw('DATE(orders.created_at) as date, COUNT(DISTINCT order_items.product_id) as unique_products_sold')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Category performance
        $categoryPerformance = OrderItem::select('categories.name as category_name')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.quantity) as total_quantity, SUM(order_items.total_price) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        return Inertia::render('Admin/Analytics/Products', [
            'top_selling_by_qty' => $topSellingByQty,
            'top_selling_by_revenue' => $topSellingByRevenue,
            'low_stock_products' => $lowStockProducts,
            'product_performance' => $productPerformance,
            'category_performance' => $categoryPerformance,
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    public function customers(Request $request): Response
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subDays(30);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        // New customers over time
        $newCustomers = User::selectRaw('DATE(created_at) as date, COUNT(*) as new_customers')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top customers by spending
        $topCustomers = User::select('users.id', 'users.name', 'users.email')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(orders.total_amount) as total_spent, COUNT(orders.id) as total_orders')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get();

        // Customer lifetime value
        $customerLTV = User::select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('SUM(orders.total_amount) as lifetime_value, COUNT(orders.id) as total_orders')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderByDesc('lifetime_value')
            ->limit(20)
            ->get();

        // Customer segments
        $customerSegments = [
            'new' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active' => User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })->count(),
            'with_subscriptions' => User::whereHas('subscriptions', function ($query) {
                $query->where('status', 'active');
            })->count(),
        ];

        // Repeat purchase rate
        $repeatCustomers = User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }, '>=', 2)->count();

        $totalCustomersWithOrders = User::whereHas('orders', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        })->count();

        $repeatPurchaseRate = $totalCustomersWithOrders > 0 ? ($repeatCustomers / $totalCustomersWithOrders) * 100 : 0;

        return Inertia::render('Admin/Analytics/Customers', [
            'new_customers' => $newCustomers,
            'top_customers' => $topCustomers,
            'customer_ltv' => $customerLTV,
            'customer_segments' => $customerSegments,
            'repeat_purchase_rate' => $repeatPurchaseRate,
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
        ]);
    }

    private function getMetrics(Carbon $dateFrom, Carbon $dateTo): array
    {
        return [
            'total_revenue' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total_amount'),
            'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'average_order_value' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->avg('total_amount') ?? 0,
            'new_customers' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active_subscriptions' => Subscription::where('status', 'active')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
        ];
    }

    private function calculateComparison(array $current, array $previous): array
    {
        $comparison = [];
        
        foreach ($current as $key => $value) {
            $previousValue = $previous[$key] ?? 0;
            
            if ($previousValue > 0) {
                $percentageChange = (($value - $previousValue) / $previousValue) * 100;
            } else {
                $percentageChange = $value > 0 ? 100 : 0;
            }
            
            $comparison[$key] = [
                'current' => $value,
                'previous' => $previousValue,
                'change' => $value - $previousValue,
                'percentage_change' => round($percentageChange, 2),
            ];
        }
        
        return $comparison;
    }

    private function getRevenueTrend(Carbon $dateFrom, Carbon $dateTo): array
    {
        return Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getTopProducts(Carbon $dateFrom, Carbon $dateTo, int $limit = 10): array
    {
        return OrderItem::select('products.name', 'products.sku')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.quantity) as total_sold, SUM(order_items.total_price) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function getSalesByCategory(Carbon $dateFrom, Carbon $dateTo): array
    {
        return Category::select('categories.name')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(order_items.total_price) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    private function getCustomerAcquisition(Carbon $dateFrom, Carbon $dateTo): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as new_customers')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function calculateConversionRate(Carbon $dateFrom, Carbon $dateTo): float
    {
        $totalVisitors = User::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalOrders = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        
        return $totalVisitors > 0 ? ($totalOrders / $totalVisitors) * 100 : 0;
    }
}