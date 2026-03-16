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
        Schema::create('crm_armada_rows', function (Blueprint $table) {
            $table->char('id', 36)->nullable();
            $table->char('report_id', 36)->nullable();
            $table->string('nopol', 58)->nullable();
            $table->string('status', 18)->nullable();
            $table->string('tipe_armada', 33)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->decimal('bayar_os', 9)->nullable();
            $table->string('rekomendasi', 343)->nullable();
            $table->string('created_at', 29)->nullable();
            $table->string('bukti', 325)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_armada_rows');
    }
};
