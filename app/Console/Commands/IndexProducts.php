<?php

namespace App\Console\Commands;

use App\Services\SearchService;
use App\Models\Product;
use Illuminate\Console\Command;

class IndexProducts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'search:index
                            {--fresh : Delete existing index and create fresh}
                            {--chunk=100 : Number of products to index per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Index all products to Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting product indexing...');

        if ($this->option('fresh')) {
            $this->info('Clearing existing index...');
            Product::removeAllFromSearch();
        }

        $chunkSize = (int) $this->option('chunk');
        $totalProducts = Product::count();
        
        if ($totalProducts === 0) {
            $this->warn('No products found to index.');
            return self::SUCCESS;
        }

        $this->info("Indexing {$totalProducts} products in chunks of {$chunkSize}...");
        
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        try {
            Product::chunk($chunkSize, function ($products) use ($bar) {
                foreach ($products as $product) {
                    $product->searchable();
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info('✅ Product indexing completed successfully!');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine();
            $this->error('❌ Indexing failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}