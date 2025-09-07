<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $dashboardData = [];

        if ($user->hasRole('admin')) {
            $dashboardData = $this->getAdminDashboardData();
        } elseif ($user->hasRole('seller')) {
            $dashboardData = $this->getSellerDashboardData($user);
        } else {
            $dashboardData = $this->getBuyerDashboardData($user);
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => $dashboardData,
            'userRole' => $user->roles->first()->name ?? 'buyer',
        ]);
    }

    private function getAdminDashboardData(): array
    {
        return [
            'totalUsers' => \App\Models\User::count(),
            'totalProducts' => \App\Models\Product::count(),
            'totalOrders' => \App\Models\Order::count(),
            'totalRevenue' => \App\Models\Order::where('payment_status', 'paid')->sum('total_amount'),
            'recentOrders' => \App\Models\Order::with('user')
                ->latest()
                ->take(5)
                ->get(),
            'topProducts' => \App\Models\Product::withCount('orderItems')
                ->orderBy('order_items_count', 'desc')
                ->take(5)
                ->get(),
        ];
    }

    private function getSellerDashboardData($user): array
    {
        return [
            'totalProducts' => $user->products()->count(),
            'activeProducts' => $user->products()->where('status', 'active')->count(),
            'totalOrders' => \App\Models\OrderItem::whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'totalRevenue' => \App\Models\OrderItem::whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereHas('order', function ($query) {
                $query->where('payment_status', 'paid');
            })->sum('total_price'),
            'recentOrders' => \App\Models\OrderItem::with(['order.user', 'product'])
                ->whereHas('product', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()
                ->take(5)
                ->get(),
            'lowStockProducts' => $user->products()
                ->where('track_quantity', true)
                ->where('quantity', '<=', config('smartmart.shop.low_stock_threshold', 5))
                ->get(),
        ];
    }

    private function getBuyerDashboardData($user): array
    {
        return [
            'totalOrders' => $user->orders()->count(),
            'pendingOrders' => $user->orders()->where('status', 'pending')->count(),
            'totalSpent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'wishlistCount' => $user->wishlist()->count(),
            'recentOrders' => $user->orders()
                ->with('items.product')
                ->latest()
                ->take(5)
                ->get(),
            'recommendations' => \App\Models\Product::active()
                ->inRandomOrder()
                ->take(4)
                ->get(),
        ];
    }
}