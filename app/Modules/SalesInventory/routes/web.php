<?php

use App\Modules\SalesInventory\Http\Controllers\CustomerController;
use App\Modules\SalesInventory\Http\Controllers\ItemController;
use App\Modules\SalesInventory\Http\Controllers\SalesOrderController;
use App\Modules\SalesInventory\Http\Controllers\StockAdjustmentController;
use App\Modules\SalesInventory\Http\Controllers\StockMovementController;
use App\Modules\SalesInventory\Http\Controllers\ItemCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('sales')->name('sales.')->group(function () {
    Route::resource('items', ItemController::class)->except(['show', 'destroy']);
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

    Route::get('orders', [SalesOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [SalesOrderController::class, 'create'])->name('orders.create');
    Route::get('orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/complete', [SalesOrderController::class, 'complete'])->name('orders.complete');
    Route::post('orders/{order}/cancel', [SalesOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('items/{item}/adjust', [StockAdjustmentController::class, 'create'])->name('stock.adjust');
    Route::post('items/{item}/adjust', [StockAdjustmentController::class, 'store'])->name('stock.adjust.store');
    Route::get('items/{item}/movements', [StockMovementController::class, 'index'])->name('stock.movements');

    Route::get('items/categories', [ItemCategoryController::class, 'index'])->name('categories.index');
    Route::post('items/categories', [ItemCategoryController::class, 'store'])->name('categories.store');
});
