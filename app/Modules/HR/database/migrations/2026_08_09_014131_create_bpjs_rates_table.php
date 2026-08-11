<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_rates', function (Blueprint $table) {
            $table->id();
            // enum: kesehatan, jht, jkk, jkm, jp
            $table->string('bpjs_type', 30);
            $table->decimal('rate_employee_percentage', 5, 2);
            $table->decimal('rate_company_percentage', 5, 2);
            $table->decimal('max_wage_base', 15, 2)->nullable();
            $table->date('effective_date');
            $table->date('end_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_rates');
    }
};
