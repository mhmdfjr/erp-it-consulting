<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('employee_code', 50)->unique();
            $table->string('full_name', 255);
            $table->string('nik', 20)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('gender', 10);
            $table->date('birth_date');
            // enum: TK0, TK1, TK2, TK3, K0, K1, K2, K3
            $table->string('ptkp_status', 10);
            $table->foreignId('position_id')->constrained('positions');
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            // enum: active, resigned, terminated
            $table->string('employment_status', 20)->default('active');
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('position_id');
            $table->index('employment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
