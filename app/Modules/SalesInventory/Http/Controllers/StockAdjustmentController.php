<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Http\Requests\StoreStockAdjustmentRequest;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Services\InventoryService;

class StockAdjustmentController extends Controller
{
    public function create(Item $item)
    {
        $this->authorize('sales.inventory.adjust', $item);

        return view('sales::stock.adjust', compact('item'));
    }

    public function store(StoreStockAdjustmentRequest $request, Item $item, InventoryService $service)
    {
        $data = $request->validated();

        $service->recordAdjustment(
            $item,
            $data['quantity'],
            $data['direction'],
            $data['reason_code'],
            $data['note'] ?? null,
        );

        return redirect()
            ->route('sales.stock.movements', $item)
            ->with('success', 'Stock adjustment berhasil dicatat.');
    }
}
