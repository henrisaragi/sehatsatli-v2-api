<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrGeneralReportOfficerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_general_report_officer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_report')->nullable()->comment("kode laporan umum");
            $table->string('name')->nullable()->comment("Nama Petugas");
            $table->string('occupation')->nullable()->comment("jabatan");
            $table->string('phone')->nullable()->comment("No. Telp / HP");
            $table->string('signed')->default('0')->comment("Yes No");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_general_report_officer');
    }
}
