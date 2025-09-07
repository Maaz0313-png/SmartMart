<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GenerateAnalyticsReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes for large reports

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reportType,
        public array $parameters = [],
        public ?string $userEmail = null
    ) {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Generating analytics report', [
            'type' => $this->reportType,
            'parameters' => $this->parameters,
        ]);

        try {
            $reportData = match ($this->reportType) {
                'sales' => $this->generateSalesReport(),
                'products' => $this->generateProductsReport(),
                'customers' => $this->generateCustomersReport(),
                'inventory' => $this->generateInventoryReport(),
                default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}")
            };

            $fileName = $this->saveReportToFile($reportData);

            if ($this->userEmail) {
                $this->emailReport($fileName);
            }

            Log::info('Analytics report generated successfully', [
                'type' => $this->reportType,
                'file' => $fileName,
                'records' => count($reportData),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate analytics report', [
                'type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate sales report data.
     */
    private function generateSalesReport(): array
    {
        $startDate = $this->parameters['start_date'] ?? now()->subMonth();
        $endDate = $this->parameters['end_date'] ?? now();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'items.product'])
            ->get();

        return $orders->map(function ($order) {
            return [
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'completed_at' => $order->completed_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Generate products report data.
     */
    private function generateProductsReport(): array
    {
        $products = Product::with(['category', 'orderItems'])
            ->withCount('orderItems')
            ->get();

        return $products->map(function ($product) {
            $totalSold = $product->orderItems->sum('quantity');
            $totalRevenue = $product->orderItems->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });

            return [
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'price' => $product->price,
                'quantity_in_stock' => $product->quantity,
                'total_sold' => $totalSold,
                'total_revenue' => $totalRevenue,
                'view_count' => $product->view_count ?? 0,
                'average_rating' => $product->average_rating ?? 0,
                'is_active' => $product->is_active ? 'Yes' : 'No',
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Generate customers report data.
     */
    private function generateCustomersReport(): array
    {
        $users = \App\Models\User::with(['orders', 'subscriptions'])
            ->whereHas('orders')
            ->get();

        return $users->map(function ($user) {
            $totalSpent = $user->orders->sum('total_amount');
            $orderCount = $user->orders->count();
            $avgOrderValue = $orderCount > 0 ? $totalSpent / $orderCount : 0;

            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'total_orders' => $orderCount,
                'total_spent' => $totalSpent,
                'average_order_value' => $avgOrderValue,
                'subscription_status' => $user->subscriptions->isNotEmpty() ? 'Active' : 'None',
                'last_order' => $user->orders->max('created_at'),
                'registered_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Generate inventory report data.
     */
    private function generateInventoryReport(): array
    {
        $products = Product::with(['category', 'variants'])
            ->where('is_active', true)
            ->get();

        $inventoryData = [];

        foreach ($products as $product) {
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $inventoryData[] = [
                        'product_name' => $product->name,
                        'variant' => $variant->name,
                        'sku' => $variant->sku ?? $product->sku,
                        'quantity' => $variant->quantity,
                        'price' => $variant->price,
                        'low_stock_threshold' => $variant->low_stock_threshold ?? 10,
                        'status' => $variant->quantity <= ($variant->low_stock_threshold ?? 10) ? 'Low Stock' : 'In Stock',
                        'category' => $product->category?->name,
                    ];
                }
            } else {
                $inventoryData[] = [
                    'product_name' => $product->name,
                    'variant' => 'Default',
                    'sku' => $product->sku,
                    'quantity' => $product->quantity,
                    'price' => $product->price,
                    'low_stock_threshold' => $product->low_stock_threshold ?? 10,
                    'status' => $product->quantity <= ($product->low_stock_threshold ?? 10) ? 'Low Stock' : 'In Stock',
                    'category' => $product->category?->name,
                ];
            }
        }

        return $inventoryData;
    }

    /**
     * Save report data to a CSV file.
     */
    private function saveReportToFile(array $data): string
    {
        if (empty($data)) {
            throw new \Exception('No data to generate report');
        }

        $fileName = "reports/{$this->reportType}_report_" . now()->format('Y-m-d_H-i-s') . '.csv';

        $csvContent = $this->arrayToCsv($data);
        Storage::disk('local')->put($fileName, $csvContent);

        return $fileName;
    }

    /**
     * Convert array to CSV content.
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Email the report to the user.
     */
    private function emailReport(string $fileName): void
    {
        // In a real application, you would send an email with the report attached
        // For now, we'll just log that the email would be sent
        Log::info('Report email would be sent', [
            'email' => $this->userEmail,
            'file' => $fileName,
            'type' => $this->reportType,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Analytics report generation failed', [
            'type' => $this->reportType,
            'error' => $exception->getMessage(),
            'parameters' => $this->parameters,
        ]);
    }
}