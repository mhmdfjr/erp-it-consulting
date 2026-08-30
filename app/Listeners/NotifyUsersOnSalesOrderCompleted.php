<?php

namespace App\Listeners;

use App\Models\User;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use App\Modules\SalesInventory\Models\SalesOrder;
use App\Notifications\SalesOrderCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Cross-cutting concern (notifikasi), bukan milik module bisnis manapun --
 * sama alasannya dengan DashboardController yang sengaja ditaruh di luar
 * app/Modules/{Module}. Queued, konsisten dengan listener finansial lain
 * (ARCHITECTURE.md Section 5), supaya gagal kirim notifikasi tidak
 * mengganggu request "Complete Order" milik user asal.
 */
class NotifyUsersOnSalesOrderCompleted implements ShouldQueue
{
    public function handle(SalesOrderCompleted $event): void
    {
        $order = SalesOrder::with('customer')->find($event->salesOrderId);

        if (! $order) {
            return;
        }

        $users = User::permission('sales.order.view')->get();

        Notification::send($users, new SalesOrderCompletedNotification($order));
    }
}
