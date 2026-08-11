<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_payroll_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('payroll_component_id')->constrained('payroll_components');
            // dipakai kalau calculation_type = fixed_amount
            $table->decimal('amount', 15, 2)->nullable();
            // dipakai kalau calculation_type = percentage_of_base
            // basis persentase employees.base_salary kontraktual,
            $table->decimal('percentage', 5, 2)->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable();

            $table->index(['employee_id', 'payroll_component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_components');
    }
};
