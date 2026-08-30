<?php

namespace App\Support;

use App\Models\User;
use App\Modules\HR\Models\PayrollPeriod;
use App\Modules\SalesInventory\Models\Item;
use Carbon\Carbon;

/**
 * Alert "computed" (bukan event tersimpan): stok rendah dan pengingat payroll.
 * Dihitung ulang tiap kali dipanggil, BUKAN disimpan sebagai notification row --
 * supaya otomatis hilang begitu kondisinya tidak lagi terpenuhi (stok sudah
 * direstock, payroll sudah diproses), tanpa perlu mekanisme "mark as read"
 * atau job terjadwal untuk terus-menerus re-check.
 */
class NotificationCenterService
{
    public function computedAlertsFor(User $user): array
    {
        $alerts = [];

        if ($user->can('sales.inventory.view')) {
            $alerts = array_merge($alerts, $this->lowStockAlerts());
        }

        if ($user->can('hr.payroll.process')) {
            $reminder = $this->payrollReminder();
            if ($reminder) {
                $alerts[] = $reminder;
            }
        }

        return $alerts;
    }

    private function lowStockAlerts(): array
    {
        $threshold = config('erp.low_stock_threshold', 5);

        /**
         * FIX: whereRaw(), bukan havingRaw(). HAVING di SQL standar cuma
         * valid untuk filter hasil agregat/GROUP BY -- PostgreSQL menegakkan
         * ini ketat (beda dari SQLite/MySQL yang lebih longgar), lempar
         * "Grouping error" kalau dipaksakan tanpa GROUP BY. Filter ini
         * sebenarnya row-level (available stock per item), jadi WHERE yang
         * benar, bukan HAVING. Pola ini sudah benar duluan di
         * DashboardController::getLowStockItems() -- seharusnya konsisten
         * dari awal, ini kelalaian menyalin pola yang sama persis.
         */
        return Item::query()
            ->leftJoin('stock_levels', 'stock_levels.item_id', '=', 'items.id')
            ->where('items.item_type', 'physical_good')
            ->where('items.is_active', true)
            ->whereRaw('COALESCE(stock_levels.quantity_on_hand, 0) - COALESCE(stock_levels.quantity_reserved, 0) <= ?', [$threshold])
            ->selectRaw('items.id, items.name, COALESCE(stock_levels.quantity_on_hand, 0) - COALESCE(stock_levels.quantity_reserved, 0) as available')
            ->orderBy('available')
            ->limit(3)
            ->get()
            ->map(fn ($item) => [
                'title' => 'Stok Menipis',
                'message' => "{$item->name} tersisa {$item->available} unit.",
                'icon' => 'boxes',
                'color' => 'warning',
                'url' => route('sales.items.index'),
            ])
            ->all();
    }

    private function payrollReminder(): ?array
    {
        $reminderDay = config('erp.payroll_reminder_day', 25);
        $today = Carbon::today();

        if ($today->day < $reminderDay) {
            return null;
        }

        $processed = PayrollPeriod::where('period_month', $today->month)
            ->where('period_year', $today->year)
            ->whereIn('status', ['processed', 'paid'])
            ->exists();

        if ($processed) {
            return null;
        }

        return [
            'title' => 'Pengingat Payroll',
            'message' => "Payroll periode {$today->translatedFormat('F Y')} belum diproses.",
            'icon' => 'banknote',
            'color' => 'danger',
            'url' => route('hr.payroll-runs.index'),
        ];
    }
}
