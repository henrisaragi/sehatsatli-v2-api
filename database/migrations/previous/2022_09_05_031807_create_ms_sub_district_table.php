<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsSubDistrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_sub_district', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->integer('id_province');
            $table->integer('id_district');
            
            $table->foreign('id_district', 'ID_Dis')->references('id')->on('ms_district')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_province', 'ID_Pro')->references('id')->on('ms_province')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_sub_district');
    }
}
