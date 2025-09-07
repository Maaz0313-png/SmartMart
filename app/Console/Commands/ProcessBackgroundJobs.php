<?php

namespace App\Console\Commands;

use App\Jobs\GenerateProductRecommendationsJob;
use App\Jobs\GenerateAnalyticsReportJob;
use App\Jobs\SyncExternalApiDataJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessBackgroundJobs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jobs:process {type?} {--user=} {--email=}';

    /**
     * The console command description.
     */
    protected $description = 'Process various background jobs for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $userId = $this->option('user');
        $email = $this->option('email');

        if (!$type) {
            $this->showAvailableJobs();
            return 0;
        }

        $this->info("Processing background job: {$type}");

        try {
            match ($type) {
                'recommendations' => $this->processRecommendations($userId),
                'analytics' => $this->processAnalytics($email),
                'sync-inventory' => $this->processSyncJob('inventory_sync'),
                'sync-prices' => $this->processSyncJob('price_updates'),
                'sync-shipping' => $this->processSyncJob('shipping_rates'),
                'sync-exchange' => $this->processSyncJob('exchange_rates'),
                'daily-tasks' => $this->processDailyTasks(),
                default => $this->error("Unknown job type: {$type}")
            };

            $this->info('Background job dispatched successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to process job: {$e->getMessage()}");
            Log::error('Background job command failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return 1;
        }
    }

    /**
     * Show available job types.
     */
    private function showAvailableJobs(): void
    {
        $this->info('Available background job types:');
        $this->line('');
        $this->line('  <fg=green>recommendations</fg=green>    Generate product recommendations for users');
        $this->line('  <fg=green>analytics</fg=green>         Generate analytics reports');
        $this->line('  <fg=green>sync-inventory</fg=green>    Sync inventory data from external API');
        $this->line('  <fg=green>sync-prices</fg=green>       Sync price updates from external API');
        $this->line('  <fg=green>sync-shipping</fg=green>     Sync shipping rates from carriers');
        $this->line('  <fg=green>sync-exchange</fg=green>     Sync currency exchange rates');
        $this->line('  <fg=green>daily-tasks</fg=green>       Run all daily maintenance tasks');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan jobs:process recommendations --user=123');
        $this->line('  php artisan jobs:process analytics --email=admin@example.com');
        $this->line('  php artisan jobs:process daily-tasks');
    }

    /**
     * Process product recommendations.
     */
    private function processRecommendations(?string $userId): void
    {
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception("User not found: {$userId}");
            }

            GenerateProductRecommendationsJob::dispatch($userId, 'manual_trigger');
            $this->info("Product recommendations job dispatched for user: {$user->name}");
        } else {
            // Generate recommendations for all active users
            $activeUsers = User::where('is_active', true)
                ->whereHas('orders')
                ->pluck('id');

            foreach ($activeUsers as $userId) {
                GenerateProductRecommendationsJob::dispatch($userId, 'daily_recommendations');
            }

            $this->info("Product recommendations jobs dispatched for {$activeUsers->count()} users");
        }
    }

    /**
     * Process analytics reports.
     */
    private function processAnalytics(?string $email): void
    {
        $reportTypes = ['sales', 'products', 'customers', 'inventory'];

        foreach ($reportTypes as $type) {
            GenerateAnalyticsReportJob::dispatch($type, [
                'start_date' => now()->subMonth(),
                'end_date' => now(),
            ], $email);
        }

        $this->info("Analytics report jobs dispatched for all report types");
        if ($email) {
            $this->info("Reports will be emailed to: {$email}");
        }
    }

    /**
     * Process external API sync jobs.
     */
    private function processSyncJob(string $apiType): void
    {
        SyncExternalApiDataJob::dispatch($apiType);
        $this->info("External API sync job dispatched: {$apiType}");
    }

    /**
     * Process all daily tasks.
     */
    private function processDailyTasks(): void
    {
        $this->info('Running daily maintenance tasks...');

        // Generate recommendations for active users
        $this->processRecommendations(null);

        // Sync external data
        $syncTypes = ['inventory_sync', 'price_updates', 'shipping_rates', 'exchange_rates'];
        foreach ($syncTypes as $type) {
            $this->processSyncJob($type);
        }

        // Generate daily analytics reports
        $this->processAnalytics(null);

        $this->info('All daily tasks have been dispatched!');
    }
}