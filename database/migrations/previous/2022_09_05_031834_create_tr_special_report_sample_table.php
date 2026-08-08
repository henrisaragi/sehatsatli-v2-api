<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrSpecialReportSampleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_special_report_sample', function (Blueprint $table) {
            $table->increments('id');
            $table->string('special_report')->nullable()->comment("ID Laporan Khusus");
            $table->string('sample_type')->nullable()->comment("Tipe Sampel");
            $table->string('test_type')->nullable()->comment("Tipe Pengujian");
            $table->string('sample_code')->nullable()->comment("Code Sample");
            $table->dateTime('sample_date')->nullable()->comment("Tanggal pengambilan Sample");
            $table->string('sample_time')->nullable()->comment("Waktu Pengambilan Sample");
            $table->string('lab_name')->nullable()->comment("Nama Lab");
            $table->string('flag')->default('0')->comment("Jumlah ditemukan Mati");
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
        Schema::dropIfExists('tr_special_report_sample');
    }
}
