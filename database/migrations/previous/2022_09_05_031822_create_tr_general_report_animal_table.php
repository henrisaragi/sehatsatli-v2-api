<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrGeneralReportAnimalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_general_report_animal', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_report')->nullable()->comment("ID Laporan Umum");
            $table->string('name_id')->nullable()->comment("Nama Umum/ Lokal");
            $table->string('latin_name')->nullable()->comment("Nama Latin");
            $table->string('gender')->nullable()->comment("Jenis Kelamin");
            $table->string('age')->nullable()->comment("Estimasi Umur");
            $table->string('age_group')->nullable()->comment("golongan umur");
            $table->string('live')->default('0')->comment("Jumlah ditemukan Hidup");
            $table->string('dead')->default('0')->comment("Jumlah ditemukan Mati");
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
        Schema::dropIfExists('tr_general_report_animal');
    }
}
