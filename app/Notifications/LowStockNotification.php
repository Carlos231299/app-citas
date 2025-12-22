<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Stock Bajo: ' . $this->product->name,
            'message' => "Quedan solo {$this->product->stock} unidades.",
            'icon' => 'bi-exclamation-triangle',
            'color' => 'danger',
            'url' => route('products.index', ['highlight_product' => $this->product->id]),
            'action_type' => 'product',
            'action_id' => $this->product->id
        ];
    }
}
