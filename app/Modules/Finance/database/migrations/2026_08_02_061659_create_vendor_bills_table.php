<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->restrictOnDelete();
            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();
            $table->string('bill_number', 100);
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('unpaid'); // enum: unpaid, paid, void
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bills');
    }
};
