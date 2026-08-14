<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('sales.category.manage', ItemCategory::class);

        $categories = ItemCategory::withCount('items')->orderBy('name')->get();

        return view('sales::items.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('sales.category.manage', ItemCategory::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:item_categories,name'],
        ]);

        ItemCategory::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }
}
