<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display admin dashboard with analytics.
     */
    public function index(Request $request): Response
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays((int) $period);

        $analytics = [
            'overview' => $this->getOverviewStats($startDate),
            'revenue' => $this->getRevenueStats($startDate),
            'products' => $this->getProductStats(),
            'users' => $this->getUserStats($startDate),
            'orders' => $this->getOrderStats($startDate),
            'subscriptions' => $this->getSubscriptionStats(),
            'charts' => [
                'revenue_chart' => $this->getRevenueChartData($startDate),
                'orders_chart' => $this->getOrdersChartData($startDate),
                'users_chart' => $this->getUsersChartData($startDate),
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'analytics' => $analytics,
            'period' => $period,
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats($startDate): array
    {
        $totalRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $previousPeriodRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate->copy()->subDays($startDate->diffInDays(now())))
            ->where('created_at', '<', $startDate)
            ->sum('total_amount');

        $revenueGrowth = $previousPeriodRevenue > 0 
            ? (($totalRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100 
            : 0;

        $totalOrders = Order::where('created_at', '>=', $startDate)->count();
        $totalUsers = User::where('created_at', '>=', $startDate)->count();
        $activeSubscriptions = Subscription::active()->count();

        return [
            'total_revenue' => $totalRevenue,
            'revenue_growth' => round($revenueGrowth, 2),
            'total_orders' => $totalOrders,
            'total_users' => $totalUsers,
            'active_subscriptions' => $activeSubscriptions,
            'conversion_rate' => $this->calculateConversionRate($startDate),
        ];
    }

    /**
     * Get revenue statistics.
     */
    private function getRevenueStats($startDate): array
    {
        return [
            'today' => Order::where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'this_week' => Order::where('payment_status', 'paid')
                ->where('created_at', '>=', now()->startOfWeek())
                ->sum('total_amount'),
            'this_month' => Order::where('payment_status', 'paid')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('total_amount'),
            'avg_order_value' => Order::where('payment_status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get product statistics.
     */
    private function getProductStats(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'low_stock_products' => Product::whereRaw('quantity <= min_quantity')->count(),
            'out_of_stock_products' => Product::where('quantity', 0)->count(),
            'top_selling' => $this->getTopSellingProducts(),
        ];
    }

    /**
     * Get user statistics.
     */
    private function getUserStats($startDate): array
    {
        return [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get order statistics.
     */
    private function getOrderStats($startDate): array
    {
        $orders = Order::where('created_at', '>=', $startDate);

        return [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get subscription statistics.
     */
    private function getSubscriptionStats(): array
    {
        return [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'cancelled_subscriptions' => Subscription::cancelled()->count(),
            'paused_subscriptions' => Subscription::where('status', 'paused')->count(),
            'monthly_recurring_revenue' => Subscription::active()
                ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                ->where('subscription_plans.billing_cycle', 'monthly')
                ->sum('subscriptions.price'),
        ];
    }

    /**
     * Get revenue chart data.
     */
    private function getRevenueChartData($startDate): array
    {
        $data = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M j')),
            'revenue' => $data->pluck('revenue'),
            'orders' => $data->pluck('orders'),
        ];
    }

    /**
     * Get orders chart data.
     */
    private function getOrdersChartData($startDate): array
    {
        $data = Order::where('created_at', '>=', $startDate)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $data->pluck('status'),
            'data' => $data->pluck('count'),
        ];
    }

    /**
     * Get users chart data.
     */
    private function getUsersChartData($startDate): array
    {
        $data = User::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M j')),
            'data' => $data->pluck('count'),
        ];
    }

    /**
     * Calculate conversion rate.
     */
    private function calculateConversionRate($startDate): float
    {
        $totalUsers = User::where('created_at', '>=', $startDate)->count();
        $usersWithOrders = Order::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');

        return $totalUsers > 0 ? round(($usersWithOrders / $totalUsers) * 100, 2) : 0;
    }

    /**
     * Get top selling products.
     */
    private function getTopSellingProducts(): array
    {
        return Product::select('products.*')
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                });
            }])
            ->orderByDesc('order_items_count')
            ->take(10)
            ->get()
            ->toArray();
    }
}