<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('period_month');
            $table->smallInteger('period_year');
            // enum: draft, processed, paid
            $table->string('status', 20)->default('draft');
            $table->timestampTz('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
