<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('user_product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // for guest users
            $table->timestamp('viewed_at');
            $table->integer('view_count')->default(1);

            $table->index(['user_id', 'viewed_at']);
            $table->index(['product_id', 'viewed_at']);
            $table->index(['session_id', 'viewed_at']);
        });

        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('query');
            $table->integer('results_count')->default(0);
            $table->json('filters')->nullable(); // applied filters
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('searched_at');

            $table->index(['user_id', 'searched_at']);
            $table->index(['query', 'searched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('user_product_views');
        Schema::dropIfExists('wishlists');
    }
};