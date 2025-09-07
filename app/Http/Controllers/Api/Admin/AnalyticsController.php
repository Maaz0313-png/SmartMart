<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get admin analytics dashboard data
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);

        return response()->json([
            'summary' => $this->getSummaryStats($startDate),
            'sales' => $this->getSalesData($startDate),
            'products' => $this->getProductStats($startDate),
            'customers' => $this->getCustomerStats($startDate),
            'recent_orders' => $this->getRecentOrders(),
            'top_products' => $this->getTopProducts($startDate),
        ]);
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats(Carbon $startDate): array
    {
        return [
            'total_orders' => Order::where('created_at', '>=', $startDate)->count(),
            'total_revenue' => Order::where('created_at', '>=', $startDate)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
            'total_customers' => User::role('buyer')->where('created_at', '>=', $startDate)->count(),
            'total_products' => Product::where('status', 'approved')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', 10)->count(),
        ];
    }

    /**
     * Get sales data for charts
     */
    private function getSalesData(Carbon $startDate): array
    {
        $salesByDay = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return [
            'daily_sales' => $salesByDay,
            'total_revenue' => $salesByDay->sum('revenue'),
            'total_orders' => $salesByDay->sum('orders'),
            'average_order_value' => $salesByDay->sum('orders') > 0 
                ? $salesByDay->sum('revenue') / $salesByDay->sum('orders') 
                : 0,
        ];
    }

    /**
     * Get product statistics
     */
    private function getProductStats(Carbon $startDate): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'pending_approval' => Product::where('status', 'pending')->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'categories_count' => DB::table('categories')->count(),
        ];
    }

    /**
     * Get customer statistics
     */
    private function getCustomerStats(Carbon $startDate): array
    {
        return [
            'total_customers' => User::role('buyer')->count(),
            'new_customers' => User::role('buyer')->where('created_at', '>=', $startDate)->count(),
            'active_customers' => User::role('buyer')
                ->whereHas('orders', function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                })->count(),
            'total_sellers' => User::role('seller')->count(),
        ];
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders(): array
    {
        return Order::with(['user:id,name,email', 'items.product:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(Carbon $startDate): array
    {
        return Product::select('products.id', 'products.name', 'products.price')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as total_revenue')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get()
            ->toArray();
    }
}