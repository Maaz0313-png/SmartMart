<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily maintenance tasks
        $schedule->command('jobs:process daily-tasks')
            ->daily()
            ->at('02:00')
            ->description('Run daily maintenance tasks');

        // Generate product recommendations twice daily
        $schedule->command('jobs:process recommendations')
            ->twiceDaily(9, 17)
            ->description('Generate product recommendations');

        // Sync inventory data every 4 hours
        $schedule->command('jobs:process sync-inventory')
            ->everyFourHours()
            ->description('Sync inventory from external systems');

        // Sync prices daily
        $schedule->command('jobs:process sync-prices')
            ->daily()
            ->at('06:00')
            ->description('Sync product prices');

        // Sync shipping rates twice daily
        $schedule->command('jobs:process sync-shipping')
            ->twiceDaily(8, 20)
            ->description('Sync shipping rates');

        // Sync exchange rates hourly
        $schedule->command('jobs:process sync-exchange')
            ->hourly()
            ->description('Sync currency exchange rates');

        // Generate weekly analytics reports
        $schedule->command('jobs:process analytics')
            ->weekly()
            ->sundays()
            ->at('01:00')
            ->description('Generate weekly analytics reports');

        // Clean up old notifications (keep last 30 days)
        $schedule->call(function () {
            \App\Models\Notification::where('created_at', '<', now()->subDays(30))->delete();
        })->daily()->at('03:00')->description('Clean up old notifications');

        // Clean up expired OTP codes
        $schedule->call(function () {
            \App\Models\User::whereNotNull('otp_expires_at')
                ->where('otp_expires_at', '<', now())
                ->update(['otp_code' => null, 'otp_expires_at' => null]);
        })->hourly()->description('Clean up expired OTP codes');

        // Monitor job queue health
        $schedule->command('jobs:monitor')
            ->everyThirtyMinutes()
            ->description('Monitor background job queues');

        // Backup database daily
        $schedule->command('backup:run')
            ->daily()
            ->at('01:00')
            ->description('Daily database backup')
            ->skip(function () {
                return !app()->environment('production');
            });

        // Clear application cache weekly
        $schedule->command('cache:clear')
            ->weekly()
            ->sundays()
            ->at('04:00')
            ->description('Clear application cache');

        // Optimize application weekly
        $schedule->command('optimize')
            ->weekly()
            ->sundays()
            ->at('04:30')
            ->description('Optimize application performance');

        // Index products for search (if data has changed)
        $schedule->command('search:index-products')
            ->twiceDaily(6, 18)
            ->description('Update search index for products');

        // Send subscription renewal reminders
        $schedule->call(function () {
            $subscriptions = \App\Models\Subscription::where('status', 'active')
                ->whereDate('next_billing_date', '=', now()->addDays(3)->toDateString())
                ->with('user')
                ->get();

            foreach ($subscriptions as $subscription) {
                // Send renewal reminder notification
                $subscription->user->notify(
                    new \App\Notifications\SubscriptionRenewalReminderNotification($subscription)
                );
            }
        })->daily()->at('10:00')->description('Send subscription renewal reminders');

        // Process abandoned carts (send reminder emails)
        $schedule->call(function () {
            $abandonedCarts = \App\Models\Cart::with('user')
                ->whereHas('items')
                ->where('updated_at', '<', now()->subHours(24))
                ->where('updated_at', '>', now()->subDays(3))
                ->get();

            foreach ($abandonedCarts as $cart) {
                // Send abandoned cart reminder
                if ($cart->user) {
                    $cart->user->notify(
                        new \App\Notifications\AbandonedCartReminderNotification($cart)
                    );
                }
            }
        })->daily()->at('11:00')->description('Send abandoned cart reminders');

        // Generate low stock alerts
        $schedule->call(function () {
            $lowStockProducts = \App\Models\Product::where('quantity', '<=', 10)
                ->where('is_active', true)
                ->get();

            if ($lowStockProducts->isNotEmpty()) {
                $adminUsers = \App\Models\User::role('admin')->get();

                foreach ($adminUsers as $admin) {
                    $admin->notify(
                        new \App\Notifications\LowStockAlertNotification($lowStockProducts)
                    );
                }
            }
        })->daily()->at('08:00')->description('Generate low stock alerts');

        // GDPR compliance monitoring
        $schedule->command('gdpr:monitor --check-overdue')
            ->daily()
            ->at('07:00')
            ->description('Check for overdue GDPR requests');

        $schedule->command('gdpr:monitor --cleanup-expired')
            ->daily()
            ->at('02:30')
            ->description('Clean up expired GDPR export files');

        $schedule->command('gdpr:monitor --report')
            ->weekly()
            ->mondays()
            ->at('05:00')
            ->description('Generate weekly GDPR compliance report');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}