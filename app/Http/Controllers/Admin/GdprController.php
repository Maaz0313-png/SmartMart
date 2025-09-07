<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\User;
use App\Services\GdprService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GdprController extends Controller
{
    protected GdprService $gdprService;

    public function __construct(GdprService $gdprService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->gdprService = $gdprService;
    }

    /**
     * Display GDPR requests
     */
    public function index(Request $request): Response
    {
        $query = DataRequest::with('user:id,name,email')
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Privacy/Requests', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'type', 'search']),
            'stats' => [
                'pending' => DataRequest::where('status', 'pending')->count(),
                'processing' => DataRequest::where('status', 'processing')->count(),
                'completed' => DataRequest::where('status', 'completed')->count(),
                'rejected' => DataRequest::where('status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Show specific GDPR request
     */
    public function show(DataRequest $dataRequest): Response
    {
        $dataRequest->load('user');

        return Inertia::render('Admin/Privacy/Show', [
            'request' => $dataRequest,
        ]);
    }

    /**
     * Update GDPR request status
     */
    public function update(Request $request, DataRequest $dataRequest)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,process',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'admin_notes' => $request->admin_notes,
        ];

        switch ($request->action) {
            case 'approve':
                $updateData['status'] = DataRequest::STATUS_PROCESSING;
                $updateData['processed_at'] = now();
                
                // Queue the processing job
                \App\Jobs\ProcessGdprRequestJob::dispatch($dataRequest);
                break;

            case 'reject':
                $updateData['status'] = DataRequest::STATUS_REJECTED;
                $updateData['processed_at'] = now();
                break;

            case 'process':
                $updateData['status'] = DataRequest::STATUS_PROCESSING;
                $updateData['processed_at'] = now();
                
                // Queue the processing job
                \App\Jobs\ProcessGdprRequestJob::dispatch($dataRequest);
                break;
        }

        $dataRequest->update($updateData);

        return back()->with('success', 'Request updated successfully.');
    }

    /**
     * Bulk update GDPR requests
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:data_requests,id',
        ]);

        $requests = DataRequest::whereIn('id', $request->request_ids);

        switch ($request->action) {
            case 'approve':
                $requests->update([
                    'status' => DataRequest::STATUS_PROCESSING,
                    'processed_at' => now(),
                ]);
                
                // Queue processing jobs
                foreach ($requests->get() as $dataRequest) {
                    \App\Jobs\ProcessGdprRequestJob::dispatch($dataRequest);
                }
                break;

            case 'reject':
                $requests->update([
                    'status' => DataRequest::STATUS_REJECTED,
                    'processed_at' => now(),
                ]);
                break;

            case 'delete':
                $requests->delete();
                break;
        }

        return back()->with('success', 'Requests updated successfully.');
    }

    /**
     * Export GDPR requests data
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'filters' => 'nullable|array',
        ]);

        // Implementation for exporting GDPR requests
        // This would use Laravel Excel or similar
        
        return back()->with('info', 'Export feature will be available soon.');
    }

    /**
     * GDPR compliance dashboard
     */
    public function dashboard(): Response
    {
        $stats = [
            'total_requests' => DataRequest::count(),
            'pending_requests' => DataRequest::where('status', 'pending')->count(),
            'overdue_requests' => DataRequest::where('status', 'pending')
                ->where('requested_at', '<', now()->subDays(30))
                ->count(),
            'completed_this_month' => DataRequest::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
        ];

        $recentRequests = DataRequest::with('user:id,name,email')
            ->latest()
            ->limit(10)
            ->get();

        $requestsByType = DataRequest::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        $requestsByStatus = DataRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return Inertia::render('Admin/Privacy/Dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'requestsByType' => $requestsByType,
            'requestsByStatus' => $requestsByStatus,
        ]);
    }
}