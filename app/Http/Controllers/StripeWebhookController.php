<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhooks.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe webhook payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook event: ' . $event->type);
        }

        return response('Success', 200);
    }

    /**
     * Handle subscription created.
     */
    private function handleSubscriptionCreated($stripeSubscription)
    {
        Log::info('Subscription created webhook received', ['subscription_id' => $stripeSubscription->id]);

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
                'current_period_start' => now()->createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => now()->createFromTimestamp($stripeSubscription->current_period_end),
            ]);
        }
    }

    /**
     * Handle subscription updated.
     */
    private function handleSubscriptionUpdated($stripeSubscription)
    {
        Log::info('Subscription updated webhook received', ['subscription_id' => $stripeSubscription->id]);

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if ($subscription) {
            $updateData = [
                'status' => $stripeSubscription->status,
                'current_period_start' => now()->createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => now()->createFromTimestamp($stripeSubscription->current_period_end),
            ];

            // Handle cancellation
            if ($stripeSubscription->cancel_at_period_end) {
                $updateData['cancelled_at'] = now();
            } else {
                $updateData['cancelled_at'] = null;
            }

            $subscription->update($updateData);
        }
    }

    /**
     * Handle subscription deleted.
     */
    private function handleSubscriptionDeleted($stripeSubscription)
    {
        Log::info('Subscription deleted webhook received', ['subscription_id' => $stripeSubscription->id]);

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }
    }

    /**
     * Handle successful invoice payment.
     */
    private function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Invoice payment succeeded webhook received', ['invoice_id' => $invoice->id]);

        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => now()->createFromTimestamp($invoice->period_start),
                    'current_period_end' => now()->createFromTimestamp($invoice->period_end),
                ]);

                // Create subscription box for this billing period
                $this->createSubscriptionBox($subscription);
            }
        }
    }

    /**
     * Handle failed invoice payment.
     */
    private function handleInvoicePaymentFailed($invoice)
    {
        Log::warning('Invoice payment failed webhook received', ['invoice_id' => $invoice->id]);

        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
            
            if ($subscription) {
                $subscription->update(['status' => 'past_due']);

                // Send notification to user about failed payment
                $subscription->user->notify(new \App\Notifications\PaymentFailedNotification($subscription));
            }
        }
    }

    /**
     * Handle successful payment intent.
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment intent succeeded webhook received', ['payment_intent_id' => $paymentIntent->id]);
        // Handle one-time payments if needed
    }

    /**
     * Handle failed payment intent.
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        Log::warning('Payment intent failed webhook received', ['payment_intent_id' => $paymentIntent->id]);
        // Handle failed one-time payments if needed
    }

    /**
     * Create subscription box for the billing period.
     */
    private function createSubscriptionBox(Subscription $subscription)
    {
        $boxNumber = $this->generateBoxNumber($subscription);
        
        // Create the subscription box
        $box = $subscription->boxes()->create([
            'box_number' => $boxNumber,
            'status' => 'pending',
            'products' => [], // Will be populated by admin/algorithm
            'value' => 0,
            'ship_date' => now()->addDays(7), // Ship in 7 days
        ]);

        Log::info('Subscription box created', ['box_id' => $box->id, 'subscription_id' => $subscription->id]);
    }

    /**
     * Generate unique box number.
     */
    private function generateBoxNumber(Subscription $subscription): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $count = $subscription->boxes()->count() + 1;
        
        return sprintf('%d-%s-%03d', $year, $month, $count);
    }
}