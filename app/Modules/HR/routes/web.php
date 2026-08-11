<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\PositionController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\AttendanceController;
use App\Modules\HR\Http\Controllers\PayrollComponentController;
use App\Modules\HR\Http\Controllers\EmployeePayrollComponentController;
use App\Modules\HR\Http\Controllers\PayrollRunController;

Route::middleware(['web', 'auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);
    Route::resource('positions', PositionController::class)->except(['show', 'destroy']);
    Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
    Route::resource('attendances', AttendanceController::class)->except(['show', 'destroy']);

    Route::resource('payroll-components', PayrollComponentController::class)->except(['show', 'destroy']);
    Route::prefix('employees/{employee}/payroll-components')->name('employees.payroll-components.')->group(function () {
        Route::get('/', [EmployeePayrollComponentController::class, 'index'])->name('index');
        Route::post('/', [EmployeePayrollComponentController::class, 'store'])->name('store');
        Route::delete('/{employeePayrollComponent}', [EmployeePayrollComponentController::class, 'destroy'])->name('destroy');
    });

    Route::get('payroll-runs', [PayrollRunController::class, 'index'])->name('payroll-runs.index');
    Route::get('payroll-runs/create', [PayrollRunController::class, 'create'])->name('payroll-runs.create');
    Route::post('payroll-runs', [PayrollRunController::class, 'store'])->name('payroll-runs.store');
    Route::get('payroll-runs/{period}', [PayrollRunController::class, 'show'])->name('payroll-runs.show');
    Route::post('payroll-runs/{period}/process', [PayrollRunController::class, 'process'])->name('payroll-runs.process');
    Route::post('payroll-runs/{period}/mark-as-paid', [PayrollRunController::class, 'markAsPaid'])->name('payroll-runs.mark-as-paid');
    Route::get('payroll-runs/slip/{run}', [PayrollRunController::class, 'slip'])->name('payroll-runs.slip');
    Route::delete('payroll-runs/{period}/cancel', [PayrollRunController::class, 'cancel'])->name('payroll-runs.cancel');
});
