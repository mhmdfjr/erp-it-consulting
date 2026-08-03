<?php

use App\Modules\Finance\Http\Controllers\ChartOfAccountController;
use App\Modules\Finance\Http\Controllers\JournalEntryController;
use App\Modules\Finance\Http\Controllers\VendorBillController;
use App\Modules\Finance\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('chart-of-accounts', [ChartOfAccountController::class, 'index'])->name('coa.index');

    Route::get('journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::get('journal-entries/{journalEntry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::post('journal-entries/{journalEntry}/void', [JournalEntryController::class, 'void'])->name('journal-entries.void');

    Route::resource('vendors', VendorController::class)->except(['show']);

    Route::get('vendor-bills', [VendorBillController::class, 'index'])->name('vendor-bills.index');
    Route::get('vendor-bills/create', [VendorBillController::class, 'create'])->name('vendor-bills.create');
    Route::post('vendor-bills', [VendorBillController::class, 'store'])->name('vendor-bills.store');
    Route::get('vendor-bills/{vendorBill}', [VendorBillController::class, 'show'])->name('vendor-bills.show');
    Route::post('vendor-bills/{vendorBill}/mark-as-paid', [VendorBillController::class, 'markAsPaid'])->name('vendor-bills.mark-as-paid');
});
