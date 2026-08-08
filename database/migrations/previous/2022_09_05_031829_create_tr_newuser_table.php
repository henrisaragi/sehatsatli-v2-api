<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrNewuserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_newuser', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable()->comment("Nama Petugas");
            $table->string('occupation')->nullable()->comment("Jabatan");
            $table->string('username')->comment("username");
            $table->string('password')->comment("password");
            $table->string('group')->nullable()->comment("User Level");
            $table->string('email')->nullable()->comment("Alamat email");
            $table->string('phone')->nullable()->comment("Nomor Telp");
            $table->string('gender')->nullable()->comment("Janis Kelamin");
            $table->string('upt_type')->nullable()->comment("Tipe UPT");
            $table->string('upt_id')->default('0')->comment("ID UPT");
            $table->string('trained')->default('1');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('tr_newuser');
    }
}
