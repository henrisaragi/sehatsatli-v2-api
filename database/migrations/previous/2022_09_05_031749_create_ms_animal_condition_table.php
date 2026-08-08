<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsAnimalConditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_animal_condition', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable()->comment("Kode");
            $table->string('name')->nullable()->comment("Kondisi");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_animal_condition');
    }
}
