<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_processing_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // marketing, analytics, cookies, essential
            $table->string('version');
            $table->timestamp('agreed_at');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('data_types')->nullable(); // personal, behavioral, location, etc.
            $table->json('processing_purposes')->nullable(); // marketing, analytics, etc.
            $table->string('retention_period')->default('7 years');
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_processing_agreements');
    }
};