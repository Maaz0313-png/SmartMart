<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProductRecommendationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $products;
    public string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $products, string $reason = 'personalized')
    {
        $this->products = $products;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Recommendations',
            'message' => 'We found some products you might like!',
            'type' => 'product_recommendation',
            'products' => collect($this->products)->map(function ($product) {
                return [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['formatted_price'],
                    'image' => $product['main_image'],
                    'url' => route('products.show', $product['slug']),
                ];
            }),
            'reason' => $this->reason,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_recommendation',
            'title' => 'New Recommendations',
            'message' => 'We found some products you might like!',
            'products' => $this->products,
            'reason' => $this->reason,
            'count' => count($this->products),
        ];
    }
}