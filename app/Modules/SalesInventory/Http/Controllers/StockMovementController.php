<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Models\Item;

class StockMovementController extends Controller
{
    public function index(Item $item)
    {
        $this->authorize('sales.inventory.view', $item);

        $movements = $item->stockMovements()->latest()->paginate(20);

        return view('sales::stock.movements', compact('item', 'movements'));
    }
}
