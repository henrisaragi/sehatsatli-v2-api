<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrEvidenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_evidence', function (Blueprint $table) {
            $table->increments('id');
            $table->string('special_report')->nullable()->comment("Kode Laporan Khusus");
            $table->string('type')->nullable()->comment("Jenis Barang Bukti");
            $table->string('amount')->nullable()->comment("Jumlah");
            $table->string('description')->nullable()->comment("Keterangan");
            $table->dateTime('created')->nullable()->comment("Tanggal Dibuat");
            $table->timestamp('modified')->useCurrent()->useCurrentOnUpdate()->comment("Tanggal di modifikasi");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_evidence');
    }
}
