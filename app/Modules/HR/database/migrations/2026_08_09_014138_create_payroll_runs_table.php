<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods');
            $table->foreignId('employee_id')->constrained('employees');

            // Kolom audit trail prorate (keputusan M3 planning, DATABASE.md Section 2.8a)
            $table->smallInteger('working_days');
            $table->smallInteger('absent_days')->default(0);

            // Nilai SETELAH prorate, bukan base_salary kontraktual mentah
            $table->decimal('base_salary', 15, 2);
            $table->decimal('gross_salary', 15, 2);

            $table->decimal('bpjs_kesehatan_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_jht_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_jp_deduction', 15, 2)->default(0);
            $table->decimal('pph21_deduction', 15, 2)->default(0);
            $table->string('ter_category_used', 5)->nullable();

            $table->decimal('total_deduction', 15, 2);
            $table->decimal('net_salary', 15, 2);

            // enum: draft, finalized, paid
            $table->string('status', 20)->default('draft');

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
