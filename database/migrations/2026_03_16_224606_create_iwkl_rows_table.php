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
        Schema::create('iwkl_rows', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->string('loket', 14)->nullable();
            $table->string('kelas', 8)->nullable();
            $table->string('nama_perusahaan', 37)->nullable();
            $table->string('nama_kapal', 53)->nullable();
            $table->string('nama_pemilik', 26)->nullable();
            $table->string('alamat', 127)->nullable();
            $table->string('no_kontak', 27)->nullable();
            $table->string('tgl_lahir', 10)->nullable();
            $table->string('kapasitas_penumpang', 3)->nullable();
            $table->string('tgl_pks', 16)->nullable();
            $table->string('tgl_berakhir_pks', 31)->nullable();
            $table->string('tgl_addendum', 10)->nullable();
            $table->string('status_pks', 9)->nullable();
            $table->string('status_pembayaran', 11)->nullable();
            $table->string('status_kapal', 10)->nullable();
            $table->text('potensi_per_bulan')->nullable();
            $table->string('persen_akt_24_23', 10)->nullable();
            $table->string('pas_besar_kecil', 5)->nullable();
            $table->string('sertifikat_keselamatan', 5)->nullable();
            $table->string('izin_trayek', 5)->nullable();
            $table->string('tgl_jatuh_tempo_sertifikat_keselamatan', 10)->nullable();
            $table->string('trayek', 22)->nullable();
            $table->string('rute_awal', 23)->nullable();
            $table->string('rute_akhir', 24)->nullable();
            $table->string('sistem_pengutipan_iwkl', 8)->nullable();
            $table->string('perhitungan_tarif', 10)->nullable();
            $table->string('tarif_borongan_disepakati', 8)->nullable();
            $table->string('seat', 5)->nullable();
            $table->string('rit', 3)->nullable();
            $table->string('tarif_dasar_iwkl', 6)->nullable();
            $table->string('hari', 3)->nullable();
            $table->string('load_factor', 3)->nullable();
            $table->string('total_perhitungan', 8)->nullable();
            $table->string('keterangan', 16)->nullable();
            $table->string('created_at', 27)->nullable();
            $table->string('updated_at', 27)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iwkl_rows');
    }
};
