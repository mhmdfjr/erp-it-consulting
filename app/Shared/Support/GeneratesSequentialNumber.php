<?php

namespace App\Shared\Support;

use Illuminate\Support\Facades\DB;

trait GeneratesSequentialNumber
{
    /**
     * Generate nomor sekuensial dengan format {PREFIX}-{YYYY}-{6 digit sequential}.
     * Sequence reset tiap tahun, dicari dari nomor terakhir tahun yang sama lalu di-increment.
     *
     *
     * WAJIB dipanggil di dalam DB transaction yang sudah terbuka oleh caller, karena method ini
     * pakai lockForUpdate() untuk mencegah race condition nomor duplikat saat dua request submit
     * bersamaan.
     * @param  string  $table   Nama tabel target, misal 'journal_entries', 'sales_orders'.
     * @param  string  $column  Nama kolom nomor, misal 'entry_number', 'order_number'.
     * @param  string  $prefix  Prefix nomor, misal 'JE', 'SO', 'INV'.
     * @param  int     $year    Tahun yang dipakai untuk segmen nomor dan pencarian sequence terakhir.
     */
    protected function generateSequentialNumber(string $table, string $column, string $prefix, int $year): string
    {
        $searchPrefix = "{$prefix}-{$year}-";

        $lastNumber = DB::table($table)
            ->where($column, 'like', "{$searchPrefix}%")
            ->lockForUpdate()
            ->orderByDesc($column)
            ->value($column);

        $nextSequence = $lastNumber
            ? ((int) substr($lastNumber, -6)) + 1
            : 1;

        if ($nextSequence > 999999) {
            throw new \RuntimeException(
                "Sequence overflow untuk {$prefix}-{$year}: sudah mencapai 999999."
            );
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $nextSequence);
    }
}
