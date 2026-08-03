<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Http\Requests\MarkVendorBillAsPaidRequest;
use App\Modules\Finance\Http\Requests\StoreVendorBillRequest;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\Vendor;
use App\Modules\Finance\Models\VendorBill;
use App\Modules\Finance\Services\VendorBillService;

class VendorBillController extends Controller
{
    public function index()
    {
        $this->authorize('finance.vendorbill.view');

        $bills = VendorBill::with('vendor')
            ->orderByDesc('bill_date')
            ->paginate(20);

        return view('finance::vendor-bills.index', compact('bills'));
    }

    public function create()
    {
        $this->authorize('finance.vendorbill.create');

        $vendors = Vendor::orderBy('name')->get();
        $accounts = ChartOfAccount::where('is_postable', true)->where('is_active', true)->orderBy('code')->get();

        return view('finance::vendor-bills.create', compact('vendors', 'accounts'));
    }

    public function store(StoreVendorBillRequest $request, VendorBillService $service)
    {
        $bill = $service->createBill([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('finance.vendor-bills.show', $bill)
            ->with('success', "Vendor bill {$bill->bill_number} berhasil dibuat, journal entry accrual otomatis ter-generate.");
    }

    public function show(VendorBill $vendorBill)
    {
        $this->authorize('finance.vendorbill.view');

        $vendorBill->load('vendor', 'account');

        $cashAndBankAccounts = ChartOfAccount::whereIn('code', ['101', '102'])->get();

        return view('finance::vendor-bills.show', compact('vendorBill', 'cashAndBankAccounts'));
    }

    public function markAsPaid(MarkVendorBillAsPaidRequest $request, VendorBill $vendorBill, VendorBillService $service)
    {
        $service->markAsPaid($vendorBill, $request->validated('payment_account_id'), $request->user()->id);

        return redirect()
            ->route('finance.vendor-bills.show', $vendorBill)
            ->with('success', "Vendor bill {$vendorBill->bill_number} ditandai lunas, journal entry pelunasan otomatis ter-generate.");
    }
}
