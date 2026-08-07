<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 100)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('order_date');
            $table->string('status', 20)->default('draft'); // draft, confirmed, invoiced, completed, cancelled
            $table->decimal('total_amount', 15, 2);
            // NOT NULL kalau status = cancelled, dicek di level aplikasi
            $table->text('cancel_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();

            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
