<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsConservationInstitutionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_conservation_institution', function (Blueprint $table) {
            $table->increments('id');
            $table->string('conservation_type')->nullable()->comment("Tipe Konservasi");
            $table->string('name')->nullable()->comment("Nama");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_conservation_institution');
    }
}
