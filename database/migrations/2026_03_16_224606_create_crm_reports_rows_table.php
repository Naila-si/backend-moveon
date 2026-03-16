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
        Schema::create('crm_reports_rows', function (Blueprint $table) {
            $table->char('id', 36)->nullable();
            $table->string('report_code', 50)->nullable();
            $table->text('created_at')->nullable();
            $table->longText('step1')->nullable();
            $table->longText('step2')->nullable();
            $table->longText('step3')->nullable();
            $table->longText('step4')->nullable();
            $table->longText('step5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_reports_rows');
    }
};
