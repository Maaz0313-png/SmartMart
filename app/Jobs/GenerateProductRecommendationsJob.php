<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\ProductRecommendationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateProductRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $reason = 'daily_recommendations'
    ) {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Generating product recommendations', [
            'user_id' => $this->userId,
            'reason' => $this->reason,
        ]);

        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('User not found for recommendations', ['user_id' => $this->userId]);
            return;
        }

        // Get user's purchase history and preferences
        $userOrders = $user->orders()->with('items.product')->get();
        $viewedProducts = $user->viewedProducts()->get();
        $preferences = $user->preferences ?? [];

        // Generate recommendations based on user behavior
        $recommendations = $this->generateRecommendations($user, $userOrders, $viewedProducts, $preferences);

        if (!empty($recommendations)) {
            // Send notification about new recommendations
            $notificationService = app(NotificationService::class);
            $notification = new ProductRecommendationNotification($recommendations, $this->reason);

            $notificationService->sendNotification(
                $user,
                $notification,
                'recommendations'
            );

            Log::info('Product recommendations generated and sent', [
                'user_id' => $this->userId,
                'recommendations_count' => count($recommendations),
            ]);
        } else {
            Log::info('No recommendations generated for user', ['user_id' => $this->userId]);
        }
    }

    /**
     * Generate product recommendations for the user.
     */
    private function generateRecommendations(User $user, $orders, $viewedProducts, array $preferences): array
    {
        // This is a simplified recommendation algorithm
        // In a real application, you might use machine learning or more sophisticated algorithms

        $recommendedProducts = [];

        // Get categories from user's purchase history
        $purchasedCategories = $orders->flatMap(function ($order) {
            return $order->items->pluck('product.category_id');
        })->unique()->filter()->values();

        // Get categories from viewed products
        $viewedCategories = $viewedProducts->pluck('category_id')->unique()->filter()->values();

        // Combine and prioritize categories
        $categoryIds = $purchasedCategories->merge($viewedCategories)->unique()->take(5);

        if ($categoryIds->isNotEmpty()) {
            // Get popular products from these categories that user hasn't purchased
            $purchasedProductIds = $orders->flatMap(function ($order) {
                return $order->items->pluck('product.id');
            })->unique();

            $products = \App\Models\Product::whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $purchasedProductIds)
                ->where('is_active', true)
                ->where('quantity', '>', 0)
                ->with(['category', 'variants'])
                ->orderBy('view_count', 'desc')
                ->take(6)
                ->get();

            $recommendedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'formatted_price' => $product->formatted_price,
                    'main_image' => $product->main_image,
                    'category' => $product->category?->name,
                    'rating' => $product->average_rating,
                ];
            })->toArray();
        }

        return $recommendedProducts;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Product recommendations job failed', [
            'user_id' => $this->userId,
            'reason' => $this->reason,
            'error' => $exception->getMessage(),
        ]);
    }
}