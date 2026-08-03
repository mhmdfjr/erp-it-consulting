<?php

namespace App\Modules\HR\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipicu setelah seluruh employee aktif selesai diproses dalam satu payroll period.
 * Ditangkap Finance module untuk generate journal entry (beban gaji, utang BPJS, utang PPh21).
 * Logic payroll run itu sendiri baru dibangun di M3, event ini cuma skeleton kontrak.
 */
class PayrollProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $payrollPeriodId
    ) {
    }
}
