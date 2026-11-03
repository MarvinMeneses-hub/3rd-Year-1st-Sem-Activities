<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Inventory stock alert',
            'message' => "{$this->product->name} is {$this->status}. Current quantity: {$this->product->quantity}. Reorder level: {$this->product->reorder_level}.",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => $this->product->quantity,
            'reorder_level' => $this->product->reorder_level,
            'status' => $this->status,
        ];
    }
}
