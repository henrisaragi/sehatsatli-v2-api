<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsAgeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_age_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable()->comment("Kode Golongan");
            $table->string('name')->nullable()->comment("Nama Golongan");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_age_group');
    }
}
