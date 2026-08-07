<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Exceptions\CancelReasonRequiredException;
use App\Modules\SalesInventory\Exceptions\OrderNotCancellableException;
use App\Modules\SalesInventory\Http\Requests\CancelSalesOrderRequest;
use App\Modules\SalesInventory\Models\SalesOrder;
use App\Modules\SalesInventory\Services\SalesOrderService;

class SalesOrderController extends Controller
{
    public function index()
    {
        $this->authorize('sales.order.view', SalesOrder::class);

        $orders = SalesOrder::with('customer')->latest('order_date')->paginate(20);

        return view('sales::orders.index', compact('orders'));
    }

    public function create()
    {
        $this->authorize('sales.order.create', SalesOrder::class);

        return view('sales::orders.create');
    }

    public function show(SalesOrder $order)
    {
        $this->authorize('sales.order.view', $order);

        $order->load(['items.item', 'customer', 'invoice']);

        return view('sales::orders.show', compact('order'));
    }

    public function complete(SalesOrder $order, SalesOrderService $service)
    {
        $this->authorize('sales.order.complete', $order);

        $order = $service->completeOrder($order);

        return redirect()
            ->route('sales.orders.show', $order)
            ->with('success', "Order \"{$order->order_number}\" selesai, invoice {$order->invoice->invoice_number} terbit.");
    }

    public function cancel(CancelSalesOrderRequest $request, SalesOrder $order, SalesOrderService $service)
    {
        try {
            $order = $service->cancelOrder($order, $request->validated('reason'));
        } catch (OrderNotCancellableException|CancelReasonRequiredException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()
            ->route('sales.orders.show', $order)
            ->with('success', "Order \"{$order->order_number}\" dibatalkan.");
    }
}
