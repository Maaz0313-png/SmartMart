<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Services\GdprService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GdprController extends Controller
{
    protected GdprService $gdprService;

    public function __construct(GdprService $gdprService)
    {
        $this->gdprService = $gdprService;
    }

    /**
     * Request data export
     */
    public function requestDataExport(Request $request)
    {
        $user = $request->user();

        // Check for existing pending request
        $existingRequest = DataRequest::where('user_id', $user->getKey())
            ->where('type', DataRequest::TYPE_EXPORT)
            ->whereIn('status', [DataRequest::STATUS_PENDING, DataRequest::STATUS_PROCESSING])
            ->first();

        if ($existingRequest) {
            return back()->withErrors(['export' => 'You already have a pending data export request.']);
        }

        $dataRequest = DataRequest::create([
            'user_id' => $user->id,
            'type' => DataRequest::TYPE_EXPORT,
            'status' => DataRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        // Queue the export job
        \App\Jobs\ProcessGdprRequestJob::dispatch($dataRequest);

        return back()->with('status', 'Your data export request has been submitted. You will receive an email when ready.');
    }

    /**
     * Request data deletion
     */
    public function requestDataDeletion(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'confirmation' => 'required|accepted',
        ]);

        $user = $request->user();

        $dataRequest = DataRequest::create([
            'user_id' => $user->getKey(),
            'type' => DataRequest::TYPE_DELETE,
            'status' => DataRequest::STATUS_PENDING,
            'reason' => $request->reason,
            'requested_at' => now(),
        ]);

        return back()->with('status', 'Your data deletion request has been submitted and will be processed within 30 days.');
    }

    /**
     * Download exported data
     */
    public function downloadExport(Request $request, DataRequest $dataRequest)
    {
        if ($dataRequest->user_id !== $request->user()->getKey()) {
            abort(403);
        }

        if ($dataRequest->status !== DataRequest::STATUS_COMPLETED || !$dataRequest->file_path) {
            abort(404);
        }

        if ($dataRequest->expires_at && $dataRequest->expires_at->isPast()) {
            return back()->withErrors(['download' => 'This download link has expired.']);
        }

        return Storage::disk('private')->download($dataRequest->file_path, 'my_data.json');
    }

    /**
     * Show GDPR dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $dataRequests = $user->dataRequests()->orderBy('created_at', 'desc')->get();
        $agreements = $user->dataProcessingAgreements()->where('is_active', true)->get();

        return inertia('Privacy/Dashboard', [
            'dataRequests' => $dataRequests,
            'agreements' => $agreements,
        ]);
    }

    /**
     * Record consent
     */
    public function recordConsent(Request $request)
    {
        $request->validate([
            'type' => 'required|in:marketing,analytics,cookies,essential',
            'version' => 'required|string',
            'data_types' => 'required|array',
            'processing_purposes' => 'required|array',
        ]);

        $this->gdprService->recordConsent($request->user(), $request->all());

        return response()->json(['message' => 'Consent recorded successfully']);
    }
}