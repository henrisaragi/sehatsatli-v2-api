<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsMonthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_month', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('number')->nullable()->comment("Nomor Bulan");
            $table->string('name')->nullable()->comment("Nama Bulan");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_month');
    }
}
