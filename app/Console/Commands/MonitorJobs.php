<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MonitorJobs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'jobs:monitor {--status=} {--queue=} {--clear-failed}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor background jobs status and queue health';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clear-failed')) {
            return $this->clearFailedJobs();
        }

        $this->showJobsOverview();
        $this->showQueueStatus();
        $this->showRecentJobs();

        return 0;
    }

    /**
     * Show jobs overview.
     */
    private function showJobsOverview(): void
    {
        $this->info('=== Background Jobs Overview ===');
        $this->line('');

        try {
            // Get job statistics from the jobs table
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            $this->line("<fg=green>Pending Jobs:</fg=green> {$pending}");
            $this->line("<fg=red>Failed Jobs:</fg=red> {$failed}");

            if ($failed > 0) {
                $this->warn("Warning: {$failed} failed jobs need attention!");
            }

        } catch (\Exception $e) {
            $this->error('Could not retrieve job statistics: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Show queue status.
     */
    private function showQueueStatus(): void
    {
        $this->info('=== Queue Status ===');
        $this->line('');

        try {
            // Check Redis connection
            $redis = Redis::connection();
            $redis->ping();
            $this->line('<fg=green>✓ Redis connection: OK</fg=green>');

            // Get queue sizes
            $queues = ['default', 'high', 'low', 'notifications', 'reports'];
            $headers = ['Queue', 'Pending', 'Processing', 'Delayed'];
            $rows = [];

            foreach ($queues as $queue) {
                $pending = $redis->llen("queues:{$queue}");
                $processing = $redis->llen("queues:{$queue}:processing");
                $delayed = $redis->zcard("queues:{$queue}:delayed");

                $rows[] = [
                    $queue,
                    $pending ?: '0',
                    $processing ?: '0',
                    $delayed ?: '0',
                ];
            }

            $this->table($headers, $rows);

        } catch (\Exception $e) {
            $this->error('Could not retrieve queue status: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Show recent jobs.
     */
    private function showRecentJobs(): void
    {
        $this->info('=== Recent Failed Jobs ===');
        $this->line('');

        try {
            $status = $this->option('status');
            $queue = $this->option('queue');

            $query = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(10);

            if ($queue) {
                $query->where('queue', $queue);
            }

            $failedJobs = $query->get();

            if ($failedJobs->isEmpty()) {
                $this->line('<fg=green>No recent failed jobs!</fg=green>');
                return;
            }

            $headers = ['ID', 'Queue', 'Class', 'Failed At', 'Exception'];
            $rows = [];

            foreach ($failedJobs as $job) {
                $payload = json_decode($job->payload, true);
                $class = $payload['displayName'] ?? 'Unknown';
                $exception = substr($job->exception, 0, 100) . '...';

                $rows[] = [
                    $job->id,
                    $job->queue,
                    $class,
                    $job->failed_at,
                    $exception,
                ];
            }

            $this->table($headers, $rows);

            if ($failedJobs->count() === 10) {
                $this->line('Showing first 10 failed jobs. Use --status or --queue options to filter.');
            }

        } catch (\Exception $e) {
            $this->error('Could not retrieve recent jobs: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Clear failed jobs.
     */
    private function clearFailedJobs(): int
    {
        if (!$this->confirm('Are you sure you want to clear all failed jobs?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            $count = DB::table('failed_jobs')->count();
            DB::table('failed_jobs')->truncate();

            $this->info("Cleared {$count} failed jobs.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to clear failed jobs: ' . $e->getMessage());
            return 1;
        }
    }
}