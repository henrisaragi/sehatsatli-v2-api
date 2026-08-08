<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsTeamCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_team_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable()->comment("Kode");
            $table->string('name')->nullable()->comment("Nama Kategori");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_team_category');
    }
}
