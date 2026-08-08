<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrFilenameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_filename', function (Blueprint $table) {
            $table->increments('id');
            $table->string('report_id')->nullable()->comment("id_laporan");
            $table->string('type')->nullable()->comment("Tipe File");
            $table->string('folder')->nullable()->comment("Lokasi Folder");
            $table->string('filename')->nullable()->comment("Nama File");
            $table->dateTime('created')->nullable()->comment("Tanggal Dibuat");
            $table->timestamp('modified')->useCurrent()->useCurrentOnUpdate()->comment("Timestamp Modifikasi data");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_filename');
    }
}
