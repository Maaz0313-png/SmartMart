<?php

namespace App\Notifications;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Cart $cart;

    /**
     * Create a new notification instance.
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
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
            ->subject('Don\'t Forget Your Cart!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You left some great items in your cart.')
            ->line('Cart Total: ' . $this->cart->formatted_total)
            ->line('Items: ' . $this->cart->items->count());

        // Add details about cart items
        foreach ($this->cart->items->take(3) as $item) {
            $message->line('• ' . $item->product->name . ' (x' . $item->quantity . ')');
        }

        if ($this->cart->items->count() > 3) {
            $message->line('... and ' . ($this->cart->items->count() - 3) . ' more items');
        }

        $message->action('Complete Your Purchase', route('cart.index'))
            ->line('Items in your cart are reserved for a limited time.')
            ->line('Thank you for shopping with SmartMart!');

        return $message;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Don\'t Forget Your Cart!',
            'message' => 'You have ' . $this->cart->items->count() . ' items waiting',
            'type' => 'abandoned_cart',
            'cart_total' => $this->cart->formatted_total,
            'items_count' => $this->cart->items->count(),
            'action_url' => route('cart.index'),
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
            'type' => 'abandoned_cart',
            'title' => 'Don\'t Forget Your Cart!',
            'message' => 'You have ' . $this->cart->items->count() . ' items waiting',
            'cart_total' => $this->cart->formatted_total,
            'items_count' => $this->cart->items->count(),
            'items' => $this->cart->items->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->formatted_unit_price,
                ];
            })->toArray(),
            'action_url' => route('cart.index'),
        ];
    }
}