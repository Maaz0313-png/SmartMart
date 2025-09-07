<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->integer('results_count')->default(0);
            $table->integer('clicked_position')->nullable();
            $table->foreignId('clicked_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->json('filters')->nullable();
            $table->string('sort_by')->nullable();
            $table->timestamp('created_at');

            $table->index(['query', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['results_count', 'created_at']);
            $table->fullText(['query']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_analytics');
    }
};