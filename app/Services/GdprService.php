<?php

namespace App\Services;

use App\Models\DataRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class GdprService
{
    /**
     * Export user data for GDPR compliance
     */
    public function exportUserData(User $user): array
    {
        return $this->gatherUserData($user);
    }

    /**
     * Anonymize user data
     */
    public function anonymizeUserData(User $user): void
    {
        $this->anonymizeUserDataPrivate($user);
    }

    /**
     * Delete user data completely
     */
    public function deleteUserData(User $user): void
    {
        // For compliance, we anonymize instead of hard delete
        $this->anonymizeUserDataPrivate($user);
    }

    /**
     * Process data export request
     */
    public function processDataExport(DataRequest $request): string
    {
        $user = $request->user;
        $exportData = $this->gatherUserData($user);

        $fileName = "user_data_{$user->id}_" . now()->format('Y-m-d_H-i-s') . '.json';
        $filePath = "gdpr/exports/{$fileName}";

        Storage::disk('private')->put($filePath, json_encode($exportData, JSON_PRETTY_PRINT));

        $request->update([
            'status' => DataRequest::STATUS_COMPLETED,
            'file_path' => $filePath,
            'completed_at' => now(),
            'expires_at' => now()->addDays(30), // File available for 30 days
        ]);

        Log::info('GDPR data export completed', [
            'user_id' => $user->getKey(),
            'request_id' => $request->getKey(),
            'file_path' => $filePath,
        ]);

        return $filePath;
    }

    /**
     * Process data deletion request
     */
    public function processDataDeletion(DataRequest $request): void
    {
        $user = $request->user;

        // Anonymize instead of hard delete to preserve referential integrity
        $this->anonymizeUserDataPrivate($user);

        $request->update([
            'status' => DataRequest::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        Log::info('GDPR data deletion completed', [
            'user_id' => $user->getKey(),
            'request_id' => $request->getKey(),
        ]);
    }

    /**
     * Gather all user data for export
     */
    private function gatherUserData(User $user): array
    {
        return [
            'personal_information' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
                'postal_code' => $user->postal_code,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'account_created' => $user->created_at->toISOString(),
                'last_updated' => $user->updated_at->toISOString(),
            ],
            'orders' => $user->orders()->with('items.product')->get()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            }),
            'reviews' => $user->reviews()->with('product')->get()->map(function ($review) {
                return [
                    'product_name' => $review->product->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at->toISOString(),
                ];
            }),
            'cart_items' => $user->cart()->with('product')->get()->map(function ($cart) {
                return [
                    'product_name' => $cart->product->name,
                    'quantity' => $cart->quantity,
                    'added_at' => $cart->created_at->toISOString(),
                ];
            }),
            'wishlist' => $user->wishlist()->get()->map(function ($product) {
                return [
                    'product_name' => $product->name,
                    'price' => $product->price,
                ];
            }),
            'notifications' => $user->notifications()->get()->map(function ($notification) {
                return [
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                ];
            }),
            'consent_records' => [],
            'data_requests' => [],
            'addresses' => [],
            'export_metadata' => [
                'generated_at' => now()->toISOString(),
                'user_id' => $user->id,
                'export_version' => '1.0',
                'format_version' => '1.0',
                'data_retention_policy' => 'Data is retained as per our privacy policy',
            ],
        ];
    }

    /**
     * Anonymize user data while preserving business relationships
     */
    private function anonymizeUserDataPrivate(User $user): void
    {
        $anonymousId = 'anonymized_' . $user->getKey() . '_' . now()->timestamp;

        $user->update([
            'name' => 'Anonymous User',
            'email' => $anonymousId . '@deleted.local',
            'phone' => null,
            'address' => null,
            'city' => null,
            'country' => null,
            'postal_code' => null,
            'date_of_birth' => null,
            'gender' => null,
            'avatar' => null,
            'is_active' => false,
            'preferences' => null,
        ]);

        // Remove personal data from orders but keep business data
        $user->orders()->update([
            'shipping_address' => ['name' => 'Anonymous User'],
        ]);

        // Remove personal reviews
        $user->reviews()->delete();

        // Clear cart and wishlist
        $user->cart()->delete();
        $user->wishlist()->detach();

        // Remove notifications
        $user->notifications()->delete();

        // Revoke all API tokens
        $user->tokens()->delete();
    }

    /**
     * Create data processing agreement record
     */
    public function recordConsent(User $user, array $data): void
    {
        $user->dataProcessingAgreements()->create([
            'type' => $data['type'],
            'version' => $data['version'],
            'agreed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'is_active' => true,
            'data_types' => $data['data_types'],
            'processing_purposes' => $data['processing_purposes'],
            'retention_period' => $data['retention_period'] ?? '7 years',
        ]);
    }

    /**
     * Generate export file for data request
     */
    public function generateExportFile(DataRequest $request): string
    {
        return $this->processDataExport($request);
    }

    /**
     * Clean up expired export files
     */
    public function cleanupExpiredExportFiles(): void
    {
        $expiredRequests = DataRequest::where('type', 'export')
            ->where('status', 'completed')
            ->where('expires_at', '<', now())
            ->whereNotNull('file_path')
            ->get();

        foreach ($expiredRequests as $request) {
            if ($request->file_path) {
                Storage::disk('private')->delete($request->file_path);
                $request->update([
                    'status' => 'expired',
                    'file_path' => null,
                ]);
            }
        }
    }

    /**
     * Get overdue data requests
     */
    public function getOverdueRequests(): \Illuminate\Database\Eloquent\Collection
    {
        return DataRequest::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->get();
    }
}