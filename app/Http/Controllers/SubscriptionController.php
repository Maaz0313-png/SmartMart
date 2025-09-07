<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->middleware('auth');
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Display subscription plans.
     */
    public function plans(): Response
    {
        $plans = SubscriptionPlan::active()->orderBy('price')->get();
        
        return Inertia::render('Subscriptions/Plans', [
            'plans' => $plans,
            'userSubscription' => Auth::user()->subscriptions()->active()->with('plan')->first(),
        ]);
    }

    /**
     * Show subscription checkout.
     */
    public function checkout(SubscriptionPlan $plan): Response
    {
        $user = Auth::user();
        
        // Check if user already has an active subscription
        if ($user->subscriptions()->active()->exists()) {
            return redirect()->route('subscriptions.manage')
                ->with('error', 'You already have an active subscription.');
        }

        return Inertia::render('Subscriptions/Checkout', [
            'plan' => $plan,
            'intent' => $user->createSetupIntent(),
        ]);
    }

    /**
     * Create subscription.
     */
    public function store(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'preferences' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Check if user already has an active subscription
        if ($user->subscriptions()->active()->exists()) {
            return back()->withErrors(['subscription' => 'You already have an active subscription.']);
        }

        try {
            // Create Stripe subscription
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $user->createOrGetStripeCustomer()->id,
                'items' => [
                    ['price' => $plan->stripe_plan_id],
                ],
                'default_payment_method' => $request->payment_method,
                'trial_period_days' => $plan->trial_days > 0 ? $plan->trial_days : null,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Create local subscription record
            $localSubscription = $user->subscriptions()->create([
                'subscription_plan_id' => $plan->id,
                'stripe_subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => now()->createFromTimestamp($subscription->current_period_start),
                'current_period_end' => now()->createFromTimestamp($subscription->current_period_end),
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'preferences' => $request->preferences ?? [],
                'price' => $plan->price,
            ]);

            return redirect()->route('subscriptions.manage')
                ->with('success', 'Subscription created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Failed to create subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Manage user subscription.
     */
    public function manage(): Response
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()
            ->with(['plan', 'boxes' => fn($query) => $query->latest()->take(5)])
            ->active()
            ->first();

        if (!$subscription) {
            return redirect()->route('subscriptions.plans')
                ->with('info', 'You don\'t have an active subscription.');
        }

        return Inertia::render('Subscriptions/Manage', [
            'subscription' => $subscription,
            'upcomingInvoice' => $this->getUpcomingInvoice($subscription),
        ]);
    }

    /**
     * Update subscription preferences.
     */
    public function updatePreferences(Request $request, Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        $request->validate([
            'preferences' => 'required|array',
            'preferences.categories' => 'nullable|array',
            'preferences.dietary_restrictions' => 'nullable|array',
            'preferences.price_range' => 'nullable|array',
        ]);

        $subscription->update([
            'preferences' => $request->preferences,
        ]);

        return back()->with('success', 'Preferences updated successfully!');
    }

    /**
     * Pause subscription.
     */
    public function pause(Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        try {
            // Update Stripe subscription
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'pause_collection' => ['behavior' => 'void'],
            ]);

            $subscription->pause();

            return back()->with('success', 'Subscription paused successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to pause subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Resume subscription.
     */
    public function resume(Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        try {
            // Update Stripe subscription
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'pause_collection' => null,
            ]);

            $subscription->resume();

            return back()->with('success', 'Subscription resumed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to resume subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Subscription $subscription)
    {
        $this->authorize('delete', $subscription);

        try {
            // Cancel Stripe subscription at period end
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            $subscription->cancel();

            return back()->with('success', 'Subscription will be cancelled at the end of the billing period.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to cancel subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Reactivate cancelled subscription.
     */
    public function reactivate(Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        try {
            // Reactivate Stripe subscription
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => false,
            ]);

            $subscription->update([
                'status' => 'active',
                'cancelled_at' => null,
            ]);

            return back()->with('success', 'Subscription reactivated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reactivate subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * View subscription history.
     */
    public function history(): Response
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(10);

        return Inertia::render('Subscriptions/History', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * View subscription boxes.
     */
    public function boxes(Subscription $subscription): Response
    {
        $this->authorize('view', $subscription);

        $subscription->load('plan');
        $boxes = $subscription->boxes()->latest()->paginate(10);

        return Inertia::render('Subscriptions/Boxes', [
            'subscription' => $subscription,
            'boxes' => $boxes,
        ]);
    }

    /**
     * Get upcoming invoice for subscription.
     */
    private function getUpcomingInvoice(Subscription $subscription)
    {
        try {
            $upcomingInvoice = $this->stripe->invoices->upcoming([
                'subscription' => $subscription->stripe_subscription_id,
            ]);

            return [
                'amount' => $upcomingInvoice->amount_due / 100,
                'currency' => strtoupper($upcomingInvoice->currency),
                'period_start' => $upcomingInvoice->period_start,
                'period_end' => $upcomingInvoice->period_end,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}