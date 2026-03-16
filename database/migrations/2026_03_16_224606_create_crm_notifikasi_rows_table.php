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
        Schema::create('crm_notifikasi_rows', function (Blueprint $table) {
            $table->string('id', 36)->nullable();
            $table->string('report_id', 12)->nullable();
            $table->string('perusahaan', 43)->nullable();
            $table->string('status', 11)->nullable();
            $table->string('note', 188)->nullable();
            $table->string('ts', 22)->nullable();
            $table->string('payload', 315)->nullable();
            $table->string('report_id_int', 10)->nullable();
            $table->string('report_uuid', 36)->nullable();
            $table->string('petugas', 24)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_notifikasi_rows');
    }
};
