<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Collection $products;

    /**
     * Create a new notification instance.
     */
    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Low Stock Alert - Action Required')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The following products are running low on stock:')
            ->line('');

        foreach ($this->products->take(10) as $product) {
            $message->line('• ' . $product->name . ' (SKU: ' . $product->sku . ') - ' . $product->quantity . ' remaining');
        }

        if ($this->products->count() > 10) {
            $message->line('... and ' . ($this->products->count() - 10) . ' more products');
        }

        $message->action('Manage Inventory', route('admin.products.index'))
                ->line('Please restock these items to avoid stockouts.')
                ->line('This is an automated alert from SmartMart.');

        return $message;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Low Stock Alert',
            'message' => $this->products->count() . ' products are running low on stock',
            'type' => 'low_stock_alert',
            'products_count' => $this->products->count(),
            'action_url' => route('admin.products.index'),
            'priority' => 'high',
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
            'type' => 'low_stock_alert',
            'title' => 'Low Stock Alert',
            'message' => $this->products->count() . ' products are running low on stock',
            'products_count' => $this->products->count(),
            'products' => $this->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $product->quantity,
                ];
            })->toArray(),
            'action_url' => route('admin.products.index'),
            'priority' => 'high',
        ];
    }
}