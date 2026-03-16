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
        Schema::create('iwkbu_loket_backup_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('loket', 24)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwkbu_loket_backup_rows');
    }
};
