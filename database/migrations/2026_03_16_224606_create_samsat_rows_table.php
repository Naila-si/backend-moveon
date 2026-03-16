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
        Schema::create('samsat_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('name', 25)->nullable();
            $table->string('loket', 6)->nullable();
            $table->string('created_at', 29)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samsat_rows');
    }
};
