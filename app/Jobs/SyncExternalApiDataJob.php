<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncExternalApiDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $apiType,
        public array $parameters = []
    ) {
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting external API sync', [
            'api_type' => $this->apiType,
            'parameters' => $this->parameters,
        ]);

        try {
            match ($this->apiType) {
                'inventory_sync' => $this->syncInventoryData(),
                'price_updates' => $this->syncPriceUpdates(),
                'shipping_rates' => $this->syncShippingRates(),
                'exchange_rates' => $this->syncExchangeRates(),
                default => throw new \InvalidArgumentException("Unknown API type: {$this->apiType}")
            };

            Log::info('External API sync completed successfully', [
                'api_type' => $this->apiType,
            ]);

        } catch (\Exception $e) {
            Log::error('External API sync failed', [
                'api_type' => $this->apiType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sync inventory data from external system.
     */
    private function syncInventoryData(): void
    {
        // Example: Sync inventory from external warehouse management system
        $response = Http::timeout(60)->get('https://api.warehouse-system.com/inventory', [
            'api_key' => config('services.warehouse.api_key'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch inventory data: ' . $response->body());
        }

        $inventoryData = $response->json()['data'] ?? [];
        $updated = 0;

        foreach ($inventoryData as $item) {
            $product = Product::where('sku', $item['sku'])->first();

            if ($product && $product->quantity !== $item['quantity']) {
                $product->update(['quantity' => $item['quantity']]);
                $updated++;
            }
        }

        Log::info('Inventory sync completed', ['updated_products' => $updated]);
    }

    /**
     * Sync price updates from external system.
     */
    private function syncPriceUpdates(): void
    {
        // Example: Sync pricing from supplier API
        $response = Http::timeout(60)->get('https://api.supplier-system.com/prices', [
            'api_key' => config('services.supplier.api_key'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch price data: ' . $response->body());
        }

        $priceData = $response->json()['data'] ?? [];
        $updated = 0;

        foreach ($priceData as $item) {
            $product = Product::where('sku', $item['sku'])->first();

            if ($product && $product->price !== $item['price']) {
                $product->update([
                    'price' => $item['price'],
                    'compare_price' => $product->price, // Store old price as compare price
                ]);
                $updated++;
            }
        }

        Log::info('Price sync completed', ['updated_products' => $updated]);
    }

    /**
     * Sync shipping rates from carrier APIs.
     */
    private function syncShippingRates(): void
    {
        // Example: Sync shipping rates from carriers like FedEx, UPS, DHL
        $carriers = ['fedex', 'ups', 'dhl'];
        $allRates = [];

        foreach ($carriers as $carrier) {
            try {
                $response = Http::timeout(30)->get("https://api.{$carrier}.com/rates", [
                    'api_key' => config("services.{$carrier}.api_key"),
                ]);

                if ($response->successful()) {
                    $rates = $response->json()['rates'] ?? [];
                    $allRates[$carrier] = $rates;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to sync rates from {$carrier}", ['error' => $e->getMessage()]);
            }
        }

        // Store shipping rates in cache or database
        cache(['shipping_rates' => $allRates], now()->addHours(6));

        Log::info('Shipping rates sync completed', ['carriers' => array_keys($allRates)]);
    }

    /**
     * Sync currency exchange rates.
     */
    private function syncExchangeRates(): void
    {
        // Example: Sync exchange rates for multi-currency support
        $response = Http::timeout(30)->get('https://api.exchangerate-api.com/v4/latest/USD');

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch exchange rates: ' . $response->body());
        }

        $rates = $response->json()['rates'] ?? [];

        // Store exchange rates in cache
        cache(['exchange_rates' => $rates], now()->addHours(1));

        Log::info('Exchange rates sync completed', ['currencies' => count($rates)]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('External API sync job failed', [
            'api_type' => $this->apiType,
            'error' => $exception->getMessage(),
            'parameters' => $this->parameters,
        ]);
    }
}