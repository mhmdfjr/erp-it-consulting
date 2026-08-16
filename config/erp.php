<?php
// config/erp.php

return [
    /*
     * Ambang batas available stock (quantity_on_hand - quantity_reserved) untuk
     * item physical_good dianggap "stock rendah" di Dashboard (task 4.4).
     * Global, bukan per-item -- keputusan sadar untuk MVP, lihat ARCHITECTURE.md
     * kalau nanti perlu di-upgrade jadi kolom minimum_stock_level per item.
     */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
];
