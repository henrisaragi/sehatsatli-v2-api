<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrGeneralReportResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_general_report_result', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_report')->nullable()->comment("Laporan Umum ID");
            $table->string('name')->nullable()->comment("nama pengujian");
            $table->string('result')->nullable()->comment("hasil lab");
            $table->string('other_result')->nullable()->comment("hasil lainnya");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_general_report_result');
    }
}
