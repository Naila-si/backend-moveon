<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iwkl_bulanan_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->integer('iwkl_id')->nullable();
            $table->integer('tahun')->nullable();
            $table->integer('bulan')->nullable();
            $table->decimal('nilai')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwkl_bulanan_rows');
    }
};
