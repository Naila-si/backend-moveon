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
        Schema::create('iwkbu_backup_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('wilayah', 9)->nullable();
            $table->string('nopol', 11)->nullable();
            $table->string('tarif', 6)->nullable();
            $table->string('golongan', 2)->nullable();
            $table->string('nominal', 7)->nullable();
            $table->string('trayek', 19)->nullable();
            $table->string('jenis', 11)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->string('pic', 24)->nullable();
            $table->string('badan_hukum', 10)->nullable();
            $table->string('nama_perusahaan', 54)->nullable();
            $table->string('alamat', 104)->nullable();
            $table->string('kelurahan', 53)->nullable();
            $table->string('kecamatan', 20)->nullable();
            $table->string('kota', 16)->nullable();
            $table->string('tgl_transaksi', 10)->nullable();
            $table->string('loket', 25)->nullable();
            $table->string('masa_berlaku', 10)->nullable();
            $table->string('masa_swdkllj', 10)->nullable();
            $table->string('status_bayar', 11)->nullable();
            $table->string('status_kendaraan', 25)->nullable();
            $table->string('outstanding', 6)->nullable();
            $table->string('konfirmasi', 27)->nullable();
            $table->string('hp', 51)->nullable();
            $table->string('nama_pemilik', 63)->nullable();
            $table->string('nik', 32)->nullable();
            $table->string('dok_perizinan', 18)->nullable();
            $table->string('tgl_bayar_os', 10)->nullable();
            $table->string('nilai_bayar_os', 6)->nullable();
            $table->string('tgl_pemeliharaan', 10)->nullable();
            $table->string('nilai_pemeliharaan_os', 6)->nullable();
            $table->string('keterangan', 131)->nullable();
            $table->string('wilayah_norm', 9)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwkbu_backup_rows');
    }
};
