<?php

namespace App\Modules\SalesInventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipicu saat status Sales Order berubah jadi 'completed' (bukan saat draft/confirmed dibuat).
 * Ditangkap Finance module untuk generate invoice + journal entry (pendapatan, HPP).
 * Logic pembuatan Sales Order itu sendiri baru dibangun di M2, event ini cuma skeleton kontrak.
 *
 * Sengaja membawa primitive (int) bukan instance Model SalesOrder, karena Model-nya
 * belum ada sampai M2 dibangun. Finance module (Listener) query ulang datanya sendiri
 * lewat reference_id, konsisten dengan pola reference_type/reference_id di journal_entries.
 */
class SalesOrderCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $salesOrderId
    ) {
    }
}
