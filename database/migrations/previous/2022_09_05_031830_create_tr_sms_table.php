<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_sms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('report_code')->nullable()->comment("Kode Laporan SMS");
            $table->string('case_type')->nullable()->comment("Penyakit / Non Penyakit");
            $table->string('location')->nullable()->comment("Nama Lokasi");
            $table->date('report_date')->nullable()->comment("Tanggal Kejadian");
            $table->string('protected')->nullable()->comment("Yes - No");
            $table->string('species_name')->nullable()->comment("Nama Spesies");
            $table->string('dead')->nullable()->comment("Jumlah Mati");
            $table->string('live')->nullable()->comment("Jumlah Hidup");
            $table->string('description')->nullable()->comment("Keterangan");
            $table->string('general_report_code')->nullable()->comment("Kode Laporan Umum");
            $table->string('phone_number')->nullable()->comment("Nomor Telepon");
            $table->string('reporter_id')->nullable()->comment("Id Pelapor");
            $table->string('status')->default('1')->comment("delete = 0, aktif = 1");
            $table->dateTime('created')->nullable()->comment("Tanggal Dibuat");
            $table->timestamp('modified')->nullable()->useCurrent()->useCurrentOnUpdate()->comment("TimeStamp Data Ditambahkan");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_sms');
    }
}
