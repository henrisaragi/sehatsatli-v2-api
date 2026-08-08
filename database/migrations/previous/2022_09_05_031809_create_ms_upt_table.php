<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsUptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_upt', function (Blueprint $table) {
            $table->increments('id');
            $table->string('upt_type')->nullable()->comment("Tipe UPT");
            $table->string('name')->nullable()->comment("Nama UPT");
            $table->string('province')->nullable()->comment("Propinsi");
            $table->string('location')->nullable()->comment("Lokasi Balai");
            $table->string('type')->nullable()->comment("TN/KSDA");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_upt');
    }
}
