<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsOfficerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_officer', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment("Nama Petugas");
            $table->string('occupation')->nullable()->comment("Jabatan");
            $table->string('upt_type')->nullable()->comment("Tipe UPT");
            $table->string('upt_id')->default('0')->comment("ID UPT");
            $table->string('email')->nullable()->comment("Alamat email");
            $table->string('phone')->nullable()->comment("Nomor Telp");
            $table->string('gender')->nullable()->comment("Janis Kelamin");
            $table->string('trained')->default('1');
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
        Schema::dropIfExists('ms_officer');
    }
}
