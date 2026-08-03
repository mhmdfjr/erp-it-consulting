<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateJournalEntryFromSalesOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SalesOrderCompleted $event): void
    {
        // TODO: implemented in M2.
        // Nanti akan: query SalesOrder by $event->salesOrderId, generate invoice,
        // panggil JournalEntryService::createEntry() untuk jurnal pendapatan + HPP
        // sesuai mapping di DATABASE.md Appendix C.
    }
}
