<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Http\Requests\StoreItemRequest;
use App\Modules\SalesInventory\Http\Requests\UpdateItemRequest;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\ItemCategory;

class ItemController extends Controller
{
    public function index()
    {
        $this->authorize('sales.item.view', Item::class);

        $items = Item::with('category')->orderBy('name')->paginate(20);

        return view('sales::items.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('sales.item.create', Item::class);

        $categories = ItemCategory::orderBy('name')->get();

        return view('sales::items.create', compact('categories'));
    }

    public function store(StoreItemRequest $request)
    {
        $item = Item::create($request->validated());

        return redirect()
            ->route('sales.items.index')
            ->with('success', "Item \"{$item->name}\" berhasil dibuat.");
    }

    public function edit(Item $item)
    {
        $this->authorize('sales.item.update', $item);

        $categories = ItemCategory::orderBy('name')->get();

        return view('sales::items.edit', compact('item', 'categories'));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return redirect()
            ->route('sales.items.index')
            ->with('success', "Item \"{$item->name}\" berhasil diperbarui.");
    }
}
