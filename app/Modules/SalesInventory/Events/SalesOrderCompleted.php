<?php

namespace App\Modules\SalesInventory\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $salesOrderId,
        public int $invoiceId,
    ) {
    }
}
