<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptkp_ter_mapping', function (Blueprint $table) {
            $table->id();
            $table->string('ptkp_status', 10)->unique();
            $table->foreignId('ter_category_id')->constrained('ter_categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptkp_ter_mapping');
    }
};
