<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Http\Requests\StoreVendorRequest;
use App\Modules\Finance\Http\Requests\UpdateVendorRequest;
use App\Modules\Finance\Models\Vendor;
use Illuminate\Database\QueryException;

class VendorController extends Controller
{
    public function index()
    {
        $this->authorize('finance.vendor.view');

        $vendors = Vendor::orderBy('name')->paginate(20);

        return view('finance::vendors.index', compact('vendors'));
    }

    public function create()
    {
        $this->authorize('finance.vendor.manage');

        return view('finance::vendors.create');
    }

    public function store(StoreVendorRequest $request)
    {
        Vendor::create($request->validated());

        return redirect()->route('finance.vendors.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        $this->authorize('finance.vendor.manage');

        return view('finance::vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return redirect()->route('finance.vendors.index')->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $this->authorize('finance.vendor.manage');

        try {
            $vendor->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Vendor tidak bisa dihapus karena masih punya vendor bill terkait.');
        }

        return redirect()->route('finance.vendors.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
