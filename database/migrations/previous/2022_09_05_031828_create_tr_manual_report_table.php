<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrManualReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_manual_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('letter_number')->nullable()->comment("Nomo Surat");
            $table->date('letter_date')->nullable();
            $table->string('upt_type')->nullable()->comment("Tipe UPT - TN/KSDA");
            $table->string('upt_name')->nullable()->comment("id UPT");
            $table->string('upt')->nullable();
            $table->string('animal_name')->nullable();
            $table->string('animal_latin_name')->nullable();
            $table->string('animal_family')->nullable();
            $table->text('final_diagnosis')->nullable();
            $table->string('location_name')->nullable();
            $table->date('report_date')->nullable()->comment("Tanggal Kejadian");
            $table->unsignedInteger('animal_live')->nullable()->comment("Jumlah Hidup");
            $table->unsignedInteger('animal_died')->nullable()->comment("Jumlah Mati");
            $table->text('examination_method')->nullable()->comment("Metode Pemeriksaan");
            $table->string('lab_result')->nullable();
            $table->text('other_follow_up')->nullable();
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
        Schema::dropIfExists('tr_manual_report');
    }
}
