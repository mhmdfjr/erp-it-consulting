<?php

namespace Tests\Unit\Modules\Finance;

use App\Modules\Finance\Listeners\CreateJournalEntryFromPayroll;
use App\Modules\Finance\Listeners\CreateJournalEntryFromSalesOrder;
use App\Modules\HR\Events\PayrollProcessed;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventListenerRegistrationTest extends TestCase
{
    public function test_sales_order_completed_men_queue_listener_yang_benar(): void
    {
        Queue::fake();

        event(new SalesOrderCompleted(salesOrderId: 1, invoiceId: 1));

        Queue::assertPushed(CallQueuedListener::class, function ($job) {
            return $job->class === CreateJournalEntryFromSalesOrder::class;
        });
    }

    public function test_payroll_processed_men_queue_listener_yang_benar(): void
    {
        Queue::fake();

        event(new PayrollProcessed(payrollPeriodId: 1));

        Queue::assertPushed(CallQueuedListener::class, function ($job) {
            return $job->class === CreateJournalEntryFromPayroll::class;
        });
    }
}
