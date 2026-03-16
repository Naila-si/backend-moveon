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
        Schema::create('manifest_submissions_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('kapal', 100)->nullable();
            $table->string('rute', 150)->nullable();
            $table->integer('total_penumpang')->nullable();
            $table->decimal('jumlah_premi', 12)->nullable();
            $table->string('agen', 100)->nullable();
            $table->string('telp', 30)->nullable();
            $table->text('foto_url')->nullable();
            $table->text('sign_url')->nullable();
            $table->integer('iwkl_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifest_submissions_rows');
    }
};
