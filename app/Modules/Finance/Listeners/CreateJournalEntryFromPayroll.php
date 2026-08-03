<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\HR\Events\PayrollProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateJournalEntryFromPayroll implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PayrollProcessed $event): void
    {
        // TODO: implemented in M3.
        // Nanti akan: query seluruh PayrollRun untuk $event->payrollPeriodId,
        // panggil JournalEntryService::createEntry() untuk jurnal beban gaji + utang BPJS + utang PPh21
        // sesuai mapping di DATABASE.md Appendix C.
    }
}
