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
        Schema::create('rkj_entries_rows', function (Blueprint $table) {
            $table->string('id', 36)->nullable();
            $table->string('pid', 36)->nullable();
            $table->string('date', 10)->nullable();
            $table->string('status', 4)->nullable();
            $table->integer('value')->nullable();
            $table->string('note', 10)->nullable();
            $table->string('created_at', 29)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rkj_entries_rows');
    }
};
