<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs');
            // null kalau item BPJS/PPh21
            $table->foreignId('payroll_component_id')->nullable()->constrained('payroll_components');
            $table->string('label', 255);
            $table->decimal('amount', 15, 2);
            // enum: earning, deduction
            $table->string('type', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};
