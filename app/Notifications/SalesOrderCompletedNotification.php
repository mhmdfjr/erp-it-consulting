<?php
// app/Notifications/SalesOrderCompletedNotification.php

namespace App\Notifications;

use App\Modules\SalesInventory\Models\SalesOrder;
use Illuminate\Notifications\Notification;

class SalesOrderCompletedNotification extends Notification
{
    public function __construct(private SalesOrder $order)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Pesanan Selesai Diproses',
            'message' => "Sales Order #{$this->order->order_number} untuk {$this->order->customer->name} telah selesai, invoice terbit.",
            'icon' => 'shopping-cart',
            'color' => 'primary',
            'url' => route('sales.orders.show', $this->order),
        ];
    }
}
