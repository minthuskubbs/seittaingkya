<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        // Only add web push if the user actually has subscriptions.
        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'title' => 'Low stock alert',
            'message' => "{$this->product->name} is low ({$this->product->stock_qty} {$this->product->unit} left).",
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Low stock alert')
            ->icon('/vendor/pwa/icon-192.png')
            ->body("{$this->product->name} is low ({$this->product->stock_qty} {$this->product->unit} left).")
            ->data(['url' => url('/products/'.$this->product->id)]);
    }
}
