<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'yearly']);
            $table->integer('trial_days')->default(0);
            $table->json('features')->nullable(); // list of plan features
            $table->string('stripe_plan_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_products')->nullable(); // max products in subscription box
            $table->json('categories')->nullable(); // allowed categories for this plan
            $table->timestamps();

            $table->index(['is_active', 'billing_cycle']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->string('stripe_subscription_id')->nullable();
            $table->string('paypal_subscription_id')->nullable();
            $table->enum('status', ['active', 'cancelled', 'past_due', 'paused', 'expired'])->default('active');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->json('preferences')->nullable(); // user preferences for subscription box
            $table->decimal('price', 10, 2); // price at time of subscription
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'current_period_end']);
        });

        Schema::create('subscription_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('box_number'); // e.g., "2024-09-001"
            $table->enum('status', ['pending', 'packed', 'shipped', 'delivered'])->default('pending');
            $table->json('products'); // products included in this box
            $table->decimal('value', 10, 2); // total value of products
            $table->date('ship_date');
            $table->json('tracking_info')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'ship_date']);
            $table->index(['status', 'ship_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_boxes');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};