<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionBox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:subscriptions.view')->only(['index', 'show']);
        $this->middleware('permission:subscriptions.update')->only(['update', 'pause', 'resume', 'cancel']);
        $this->middleware('permission:subscriptions.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $query = Subscription::with(['user', 'subscriptionPlan'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('subscriptionPlan', function ($planQuery) use ($search) {
                    $planQuery->where('name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->plan_id, function ($q, $planId) {
                $q->where('subscription_plan_id', $planId);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            });

        $subscriptions = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'paused_subscriptions' => Subscription::where('status', 'paused')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'monthly_recurring_revenue' => $this->calculateMRR(),
            'churn_rate' => $this->calculateChurnRate(),
            'average_subscription_value' => Subscription::where('status', 'active')->avg('price'),
        ];

        $plans = SubscriptionPlan::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'plans' => $plans,
            'filters' => $request->only(['search', 'status', 'plan_id', 'date_from', 'date_to']),
            'subscription_statuses' => [
                'active' => 'Active',
                'paused' => 'Paused',
                'cancelled' => 'Cancelled',
                'past_due' => 'Past Due',
                'expired' => 'Expired',
            ],
        ]);
    }

    public function show(Subscription $subscription): Response
    {
        $subscription->load([
            'user',
            'subscriptionPlan',
        ]);

        $boxes = SubscriptionBox::where('subscription_id', $subscription->id)
            ->latest('ship_date')
            ->paginate(10);

        $billingHistory = $this->getBillingHistory($subscription);

        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription,
            'boxes' => $boxes,
            'billing_history' => $billingHistory,
            'upcoming_renewal' => $subscription->current_period_end,
        ]);
    }

    public function pause(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()->withErrors(['status' => 'Only active subscriptions can be paused.']);
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'paused',
                'paused_at' => now(),
            ]);

            // Pause Stripe subscription if exists
            if ($subscription->stripe_subscription_id) {
                // TODO: Implement Stripe subscription pause
            }

            activity()
                ->performedOn($subscription)
                ->causedBy(auth()->user())
                ->log('Subscription paused by admin');
        });

        return redirect()->back()->with('success', 'Subscription paused successfully.');
    }

    public function resume(Subscription $subscription)
    {
        if ($subscription->status !== 'paused') {
            return redirect()->back()->withErrors(['status' => 'Only paused subscriptions can be resumed.']);
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'active',
                'paused_at' => null,
            ]);

            // Resume Stripe subscription if exists
            if ($subscription->stripe_subscription_id) {
                // TODO: Implement Stripe subscription resume
            }

            activity()
                ->performedOn($subscription)
                ->causedBy(auth()->user())
                ->log('Subscription resumed by admin');
        });

        return redirect()->back()->with('success', 'Subscription resumed successfully.');
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
            'refund_prorated' => 'boolean',
        ]);

        if (!in_array($subscription->status, ['active', 'paused'])) {
            return redirect()->back()->withErrors(['status' => 'This subscription cannot be cancelled.']);
        }

        DB::transaction(function () use ($request, $subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Cancel Stripe subscription if exists
            if ($subscription->stripe_subscription_id) {
                // TODO: Implement Stripe subscription cancellation
                // Handle prorated refund if requested
            }

            activity()
                ->performedOn($subscription)
                ->causedBy(auth()->user())
                ->withProperties([
                    'reason' => $request->reason,
                    'refund_prorated' => $request->refund_prorated,
                ])
                ->log('Subscription cancelled by admin');
        });

        return redirect()->back()->with('success', 'Subscription cancelled successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'subscription_ids' => 'required|array|min:1',
            'subscription_ids.*' => 'exists:subscriptions,id',
            'action' => 'required|in:pause,resume,cancel,export',
        ]);

        $subscriptions = Subscription::whereIn('id', $request->subscription_ids);
        $count = 0;

        switch ($request->action) {
            case 'pause':
                $eligible = $subscriptions->where('status', 'active')->get();
                foreach ($eligible as $subscription) {
                    $subscription->update([
                        'status' => 'paused',
                        'paused_at' => now(),
                    ]);
                }
                $count = $eligible->count();
                $message = "{$count} subscriptions paused successfully.";
                break;

            case 'resume':
                $eligible = $subscriptions->where('status', 'paused')->get();
                foreach ($eligible as $subscription) {
                    $subscription->update([
                        'status' => 'active',
                        'paused_at' => null,
                    ]);
                }
                $count = $eligible->count();
                $message = "{$count} subscriptions resumed successfully.";
                break;

            case 'cancel':
                $eligible = $subscriptions->whereIn('status', ['active', 'paused'])->get();
                foreach ($eligible as $subscription) {
                    $subscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                }
                $count = $eligible->count();
                $message = "{$count} subscriptions cancelled successfully.";
                break;

            case 'export':
                // TODO: Implement subscription export
                $message = "Export initiated for selected subscriptions.";
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function analytics(Request $request): Response
    {
        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Subscription growth
        $subscriptionGrowth = Subscription::selectRaw('DATE(created_at) as date, COUNT(*) as new_subscriptions')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Cancellation data
        $cancellations = Subscription::selectRaw('DATE(cancelled_at) as date, COUNT(*) as cancellations')
            ->whereNotNull('cancelled_at')
            ->whereBetween('cancelled_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Plan popularity
        $planPopularity = SubscriptionPlan::select('name', 'billing_cycle')
            ->withCount(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderByDesc('subscriptions_count')
            ->get();

        // Revenue by plan
        $revenueByPlan = Subscription::join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscriptions.status', 'active')
            ->selectRaw('subscription_plans.name, subscription_plans.billing_cycle, SUM(subscriptions.price) as revenue, COUNT(*) as subscribers')
            ->groupBy('subscription_plans.id', 'subscription_plans.name', 'subscription_plans.billing_cycle')
            ->get();

        return Inertia::render('Admin/Subscriptions/Analytics', [
            'subscription_growth' => $subscriptionGrowth,
            'cancellations' => $cancellations,
            'plan_popularity' => $planPopularity,
            'revenue_by_plan' => $revenueByPlan,
            'filters' => compact('dateFrom', 'dateTo'),
            'summary' => [
                'mrr' => $this->calculateMRR(),
                'arr' => $this->calculateMRR() * 12,
                'churn_rate' => $this->calculateChurnRate(),
                'ltv' => $this->calculateLTV(),
                'active_subscribers' => Subscription::where('status', 'active')->count(),
                'growth_rate' => $this->calculateGrowthRate($dateFrom, $dateTo),
            ],
        ]);
    }

    private function calculateMRR(): float
    {
        return Subscription::where('status', 'active')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('
                SUM(CASE 
                    WHEN subscription_plans.billing_cycle = "monthly" THEN subscriptions.price
                    WHEN subscription_plans.billing_cycle = "yearly" THEN subscriptions.price / 12
                    WHEN subscription_plans.billing_cycle = "quarterly" THEN subscriptions.price / 3
                    WHEN subscription_plans.billing_cycle = "weekly" THEN subscriptions.price * 4.33
                    ELSE subscriptions.price
                END) as mrr
            ')
            ->value('mrr') ?? 0;
    }

    private function calculateChurnRate(): float
    {
        $startOfMonth = now()->startOfMonth();
        $activeAtStart = Subscription::where('created_at', '<', $startOfMonth)
            ->whereIn('status', ['active', 'paused'])
            ->count();

        $cancelledThisMonth = Subscription::where('cancelled_at', '>=', $startOfMonth)
            ->count();

        return $activeAtStart > 0 ? ($cancelledThisMonth / $activeAtStart) * 100 : 0;
    }

    private function calculateLTV(): float
    {
        $avgMonthlyRevenue = $this->calculateMRR();
        $churnRate = $this->calculateChurnRate();
        $avgSubscribers = Subscription::where('status', 'active')->count();

        if ($churnRate > 0 && $avgSubscribers > 0) {
            $avgRevenuePerUser = $avgMonthlyRevenue / $avgSubscribers;
            return $avgRevenuePerUser / ($churnRate / 100);
        }

        return 0;
    }

    private function calculateGrowthRate(string $dateFrom, string $dateTo): float
    {
        $newSubscriptions = Subscription::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $cancelledSubscriptions = Subscription::whereNotNull('cancelled_at')
            ->whereBetween('cancelled_at', [$dateFrom, $dateTo])
            ->count();

        $totalAtStart = Subscription::where('created_at', '<', $dateFrom)->count();

        return $totalAtStart > 0 ? (($newSubscriptions - $cancelledSubscriptions) / $totalAtStart) * 100 : 0;
    }

    private function getBillingHistory(Subscription $subscription): array
    {
        // This would typically integrate with Stripe's billing history API
        // For now, return a mock structure
        return [
            [
                'date' => $subscription->created_at,
                'amount' => $subscription->price,
                'status' => 'paid',
                'invoice_id' => 'inv_' . str()->random(10),
            ],
            // Add more billing history entries as needed
        ];
    }
}