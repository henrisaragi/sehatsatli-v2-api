<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsVillageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_village', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->integer('id_sub_district');
            $table->integer('id_district');
            $table->integer('id_province');
            
            $table->foreign('id_district', 'ID_dist')->references('id')->on('ms_district')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_province', 'ID_Prop')->references('id')->on('ms_province')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_sub_district', 'ID_Subdis')->references('id')->on('ms_sub_district')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_village');
    }
}
