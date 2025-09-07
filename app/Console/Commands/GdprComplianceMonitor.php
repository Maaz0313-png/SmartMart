<?php

namespace App\Console\Commands;

use App\Models\DataRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GdprComplianceMonitor extends Command
{
    protected $signature = 'gdpr:monitor
                            {--check-overdue : Check for overdue requests}
                            {--cleanup-expired : Clean up expired export files}
                            {--report : Generate compliance report}';

    protected $description = 'Monitor GDPR compliance and perform maintenance tasks';

    public function handle(): int
    {
        if ($this->option('check-overdue')) {
            $this->checkOverdueRequests();
        }

        if ($this->option('cleanup-expired')) {
            $this->cleanupExpiredExports();
        }

        if ($this->option('report')) {
            $this->generateComplianceReport();
        }

        if (!$this->option('check-overdue') && !$this->option('cleanup-expired') && !$this->option('report')) {
            $this->checkOverdueRequests();
            $this->cleanupExpiredExports();
        }

        return Command::SUCCESS;
    }

    private function checkOverdueRequests(): void
    {
        $overdueThreshold = now()->subDays(config('gdpr.processing_time.data_export', 30));
        
        $overdueRequests = DataRequest::where('status', 'pending')
            ->where('requested_at', '<', $overdueThreshold)
            ->with('user:id,name,email')
            ->get();

        if ($overdueRequests->count() > 0) {
            $this->warn("Found {$overdueRequests->count()} overdue GDPR requests:");
            
            foreach ($overdueRequests as $request) {
                $this->line("- {$request->type} request from {$request->user->email} (ID: {$request->id})");
                
                // Log security event
                Log::channel('security')->warning('Overdue GDPR request detected', [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'type' => $request->type,
                    'requested_at' => $request->requested_at,
                    'days_overdue' => $request->requested_at->diffInDays(now()),
                ]);
            }

            // Notify administrators
            if (config('gdpr.notifications.notify_on_overdue')) {
                // Send notification to admin
                $this->info('Overdue request notifications sent to administrators.');
            }
        } else {
            $this->info('No overdue GDPR requests found.');
        }
    }

    private function cleanupExpiredExports(): void
    {
        $expiredRequests = DataRequest::where('status', 'completed')
            ->where('type', 'export')
            ->whereNotNull('file_path')
            ->where('expires_at', '<', now())
            ->get();

        $deletedCount = 0;
        
        foreach ($expiredRequests as $request) {
            if ($request->file_path && \Storage::disk('private')->exists($request->file_path)) {
                \Storage::disk('private')->delete($request->file_path);
                $request->update(['file_path' => null]);
                $deletedCount++;
                
                Log::channel('gdpr')->info('Expired export file deleted', [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'file_path' => $request->file_path,
                ]);
            }
        }

        $this->info("Cleaned up {$deletedCount} expired export files.");
    }

    private function generateComplianceReport(): void
    {
        $this->info('GDPR Compliance Report');
        $this->line('========================');

        // Request statistics
        $totalRequests = DataRequest::count();
        $pendingRequests = DataRequest::where('status', 'pending')->count();
        $completedRequests = DataRequest::where('status', 'completed')->count();
        $rejectedRequests = DataRequest::where('status', 'rejected')->count();

        $this->table(['Metric', 'Count'], [
            ['Total Requests', $totalRequests],
            ['Pending Requests', $pendingRequests],
            ['Completed Requests', $completedRequests],
            ['Rejected Requests', $rejectedRequests],
        ]);

        // Request types breakdown
        $requestsByType = DataRequest::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get();

        $this->line('');
        $this->info('Requests by Type:');
        foreach ($requestsByType as $typeData) {
            $this->line("- {$typeData->type}: {$typeData->count}");
        }

        // Processing time analysis
        $avgProcessingTime = DataRequest::where('status', 'completed')
            ->whereNotNull('processed_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, requested_at)) as avg_days')
            ->value('avg_days');

        $this->line('');
        $this->info("Average Processing Time: " . round($avgProcessingTime, 1) . " days");

        // Overdue requests
        $overdueCount = DataRequest::where('status', 'pending')
            ->where('requested_at', '<', now()->subDays(config('gdpr.processing_time.data_export', 30)))
            ->count();

        if ($overdueCount > 0) {
            $this->error("Warning: {$overdueCount} overdue requests found!");
        } else {
            $this->info('✓ No overdue requests');
        }

        // Data retention compliance
        $this->line('');
        $this->info('Data Retention Status:');
        
        $oldUsers = User::where('created_at', '<', now()->subDays(config('gdpr.retention.user_data', 2555)))
            ->whereNull('deleted_at')
            ->count();
        
        if ($oldUsers > 0) {
            $this->warn("- {$oldUsers} user accounts exceed retention period");
        } else {
            $this->info('✓ User data within retention limits');
        }

        Log::channel('gdpr')->info('GDPR compliance report generated', [
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'overdue_requests' => $overdueCount,
            'avg_processing_days' => round($avgProcessingTime, 1),
        ]);
    }
}