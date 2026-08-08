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
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category')->nullable();
            $table->string('field')->nullable();
            $table->string('type')->nullable();
            $table->json('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('options');
    }
};
