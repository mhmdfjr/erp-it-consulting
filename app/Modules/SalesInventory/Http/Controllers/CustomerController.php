<?php

namespace App\Modules\SalesInventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesInventory\Http\Requests\StoreCustomerRequest;
use App\Modules\SalesInventory\Http\Requests\UpdateCustomerRequest;
use App\Modules\SalesInventory\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $this->authorize('sales.customer.view', Customer::class);

        $customers = Customer::orderBy('name')->paginate(20);

        return view('sales::customers.index', compact('customers'));
    }

    public function create()
    {
        $this->authorize('sales.customer.create', Customer::class);

        return view('sales::customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return redirect()
            ->route('sales.customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil dibuat.");
    }

    public function edit(Customer $customer)
    {
        $this->authorize('sales.customer.update', $customer);

        return view('sales::customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()
            ->route('sales.customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil diperbarui.");
    }
}
