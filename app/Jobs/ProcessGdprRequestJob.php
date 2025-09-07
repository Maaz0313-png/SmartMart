<?php

namespace App\Jobs;

use App\Models\DataRequest;
use App\Services\GdprService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGdprRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;

    public function __construct(public DataRequest $dataRequest)
    {
        $this->onQueue('gdpr');
    }

    public function handle(GdprService $gdprService): void
    {
        try {
            $this->dataRequest->update(['status' => DataRequest::STATUS_PROCESSING]);

            match($this->dataRequest->type) {
                DataRequest::TYPE_EXPORT => $gdprService->processDataExport($this->dataRequest),
                DataRequest::TYPE_DELETE => $gdprService->processDataDeletion($this->dataRequest),
                default => throw new \InvalidArgumentException('Invalid GDPR request type')
            };

            // Notify user via email
            $this->dataRequest->user->notify(new \App\Notifications\GdprRequestProcessedNotification($this->dataRequest));

        } catch (\Exception $e) {
            Log::error('GDPR request processing failed', [
                'request_id' => $this->dataRequest->getKey(),
                'user_id' => $this->dataRequest->user_id,
                'error' => $e->getMessage(),
            ]);

            $this->dataRequest->update([
                'status' => DataRequest::STATUS_REJECTED,
                'admin_notes' => 'Processing failed: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }
}