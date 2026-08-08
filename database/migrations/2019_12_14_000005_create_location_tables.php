<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->integer('id_province');
        });


        Schema::create('sub_districts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->integer('id_province');
            $table->integer('id_district');
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->integer('id_sub_district');
            $table->integer('id_district');
            $table->integer('id_province');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('sub_districts');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
