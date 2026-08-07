<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->string('item_type', 20);
            $table->foreignId('item_category_id')
                ->nullable()
                ->constrained('item_categories')
                ->nullOnDelete();
            $table->string('unit_of_measure', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
