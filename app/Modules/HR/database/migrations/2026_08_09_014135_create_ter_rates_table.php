<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ter_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ter_category_id')->constrained('ter_categories');
            $table->decimal('income_lower_bound', 15, 2);
            // null berarti tidak terbatas
            $table->decimal('income_upper_bound', 15, 2)->nullable();
            $table->decimal('rate_percentage', 5, 2);
            $table->date('effective_date');
            $table->date('end_date')->nullable();

            $table->index(['ter_category_id', 'income_lower_bound']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_rates');
    }
};
