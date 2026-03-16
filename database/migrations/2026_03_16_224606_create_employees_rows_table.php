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
        Schema::create('employees_rows', function (Blueprint $table) {
            $table->string('id', 36)->nullable();
            $table->string('name', 24)->nullable();
            $table->string('handle', 10)->nullable();
            $table->string('loket', 6)->nullable();
            $table->string('created_at', 29)->nullable();
            $table->integer('samsat_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees_rows');
    }
};
