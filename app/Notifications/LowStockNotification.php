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
            'message' => "Quedan {$this->product->stock} unidades (Mínimo: {$this->product->min_stock}).",
            'icon' => 'bi-exclamation-triangle',
            'color' => 'danger',
            'url' => route('products.index', ['category_id' => $this->product->category_id])
        ];
    }
}
