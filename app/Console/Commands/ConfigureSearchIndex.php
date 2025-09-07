<?php

namespace App\Console\Commands;

use App\Services\SearchService;
use Illuminate\Console\Command;

class ConfigureSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'search:configure
                            {--force : Force reconfiguration of existing index}';

    /**
     * The console command description.
     */
    protected $description = 'Configure Meilisearch index settings';

    /**
     * Execute the console command.
     */
    public function handle(SearchService $searchService): int
    {
        $this->info('Configuring Meilisearch index...');

        try {
            $searchService->configureIndex();
            $this->info('✅ Index configuration completed successfully!');
            
            if ($this->option('force') || $this->confirm('Do you want to reindex all products now?')) {
                $this->call('search:index');
            }
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to configure index: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}