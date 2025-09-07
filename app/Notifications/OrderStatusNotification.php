<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;
    public string $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $previousStatus)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
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
            ->subject('Order Update - ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order status has been updated.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('New Status: ' . ucfirst($this->order->status))
            ->line('Total Amount: ' . $this->order->formatted_total);

        if ($this->order->status === 'shipped' && $this->order->tracking_number) {
            $message->line('Tracking Number: ' . $this->order->tracking_number);
        }

        $message->action('View Order Details', route('orders.show', $this->order))
                ->line('Thank you for shopping with SmartMart!');

        return $message;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Order Update',
            'message' => "Your order #{$this->order->order_number} is now {$this->order->status}",
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'action_url' => route('orders.show', $this->order),
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
            'type' => 'order_status',
            'title' => 'Order Update',
            'message' => "Your order #{$this->order->order_number} is now {$this->order->status}",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'previous_status' => $this->previousStatus,
            'total_amount' => $this->order->formatted_total,
            'action_url' => route('orders.show', $this->order),
        ];
    }
}