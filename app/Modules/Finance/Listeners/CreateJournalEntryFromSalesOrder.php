<?php

namespace App\Modules\Finance\Listeners;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\JournalEntryService;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use App\Modules\SalesInventory\Models\SalesOrder;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateJournalEntryFromSalesOrder implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService,
    ) {
    }

    public function handle(SalesOrderCompleted $event): void
    {
        $order = SalesOrder::with('items.item')->findOrFail($event->salesOrderId);
        $invoice = Invoice::findOrFail($event->invoiceId);

        $physicalTotal = '0.00';
        $serviceTotal = '0.00';
        $totalCogs = '0.00';

        foreach ($order->items as $line) {
            $subtotal = (string) $line->subtotal;

            if ($line->item->isPhysicalGood()) {
                $physicalTotal = bcadd($physicalTotal, $subtotal, 2);
                $cogs = bcmul((string) $line->item->cost_price, (string) $line->quantity, 2);
                $totalCogs = bcadd($totalCogs, $cogs, 2);
            } else {
                $serviceTotal = bcadd($serviceTotal, $subtotal, 2);
            }
        }

        $lines = [];

        $lines[] = [
            'account_id' => $this->accountId('103'),
            'debit' => bcadd($physicalTotal, $serviceTotal, 2),
            'credit' => 0,
            'description' => "Piutang dari Sales Order {$order->order_number}",
        ];

        if (bccomp($physicalTotal, '0', 2) > 0) {
            $lines[] = [
                'account_id' => $this->accountId('402'),
                'debit' => 0,
                'credit' => $physicalTotal,
                'description' => "Pendapatan penjualan barang - {$order->order_number}",
            ];
        }

        if (bccomp($serviceTotal, '0', 2) > 0) {
            $lines[] = [
                'account_id' => $this->accountId('401'),
                'debit' => 0,
                'credit' => $serviceTotal,
                'description' => "Pendapatan jasa konsultasi - {$order->order_number}",
            ];
        }

        if (bccomp($totalCogs, '0', 2) > 0) {
            $lines[] = [
                'account_id' => $this->accountId('501'),
                'debit' => $totalCogs,
                'credit' => 0,
                'description' => "HPP barang - {$order->order_number}",
            ];
            $lines[] = [
                'account_id' => $this->accountId('105'),
                'debit' => 0,
                'credit' => $totalCogs,
                'description' => "Realisasi persediaan keluar - {$order->order_number}",
            ];
        }

        $this->journalEntryService->createEntry([
            'entry_date' => $invoice->invoice_date,
            'reference_type' => SalesOrder::class,
            'reference_id' => $order->id,
            'description' => "Pendapatan dari Sales Order {$order->order_number}, Invoice {$invoice->invoice_number}",
            'created_by' => null,
            'lines' => $lines,
        ]);
    }

    protected function accountId(string $code): int
    {
        return ChartOfAccount::where('code', $code)->firstOrFail()->id;
    }
}
