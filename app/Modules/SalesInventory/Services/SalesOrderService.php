<?php

namespace App\Modules\SalesInventory\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use App\Modules\SalesInventory\Exceptions\CancelReasonRequiredException;
use App\Modules\SalesInventory\Exceptions\OrderNotCancellableException;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\SalesOrder;
use App\Shared\Support\GeneratesSequentialNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    use GeneratesSequentialNumber;

    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }


    // Buat sales order + line items dalam satu transaction, reservasi stok untuk tiap item physical_good.
    public function createOrder(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $orderDate = Carbon::parse($data['order_date']);

            $orderNumber = $this->generateSequentialNumber(
                table: 'sales_orders',
                column: 'order_number',
                prefix: 'SO',
                year: $orderDate->year,
            );

            $totalAmount = '0.00';
            foreach ($data['items'] as $line) {
                $subtotal = bcmul((string) $line['quantity'], (string) $line['unit_price'], 2);
                $totalAmount = bcadd($totalAmount, $subtotal, 2);
            }

            $order = SalesOrder::create([
                'order_number' => $orderNumber,
                'customer_id' => $data['customer_id'],
                'order_date' => $orderDate,
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $line) {
                $item = Item::findOrFail($line['item_id']);
                $subtotal = bcmul((string) $line['quantity'], (string) $line['unit_price'], 2);

                $order->items()->create([
                    'item_id' => $item->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                if ($item->isPhysicalGood()) {
                    $this->inventoryService->reserveStock($item, (float) $line['quantity']);
                }
            }

            return $order->fresh('items');
        });
    }

    // Batalkan order. Valid cuma dari status draft/confirmed
    // Melepas reservasi stok untuk tiap item physical_good di order.
    public function cancelOrder(SalesOrder $order, string $reason): SalesOrder
    {
        if (! $order->isCancellable()) {
            throw new OrderNotCancellableException($order->status);
        }

        if (trim($reason) === '') {
            throw new CancelReasonRequiredException();
        }

        return DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $line) {
                if ($line->item->isPhysicalGood()) {
                    $this->inventoryService->releaseReservedStock($line->item, (float) $line->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason,
            ]);

            return $order->fresh('items');
        });
    }

    // Selesaikan order: realisasi stok keluar, generate Invoice Sync, baru fire event
    // SalesOrderCompleted dengan payload sales_order_id + invoice_id.
    public function completeOrder(SalesOrder $order): SalesOrder
    {
        $invoice = DB::transaction(function () use ($order) {
            foreach ($order->items as $line) {
                if ($line->item->isPhysicalGood()) {
                    $this->inventoryService->fulfillReservedStock(
                        $line->item,
                        (float) $line->quantity,
                        [
                            'reference_type' => SalesOrder::class,
                            'reference_id' => $order->id,
                        ],
                    );
                }
            }

            $order->update(['status' => 'completed']);

            $invoiceDate = now();
            $invoiceNumber = $this->generateSequentialNumber(
                table: 'invoices',
                column: 'invoice_number',
                prefix: 'INV',
                year: $invoiceDate->year,
            );

            return Invoice::create([
                'sales_order_id' => $order->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(30),
                'amount' => $order->total_amount,
                'status' => 'unpaid',
            ]);
        });

        // Fire after commit, bukan di dalam transaction
        SalesOrderCompleted::dispatch($order->id, $invoice->id);

        return $order->fresh(['items', 'invoice']);
    }
}
