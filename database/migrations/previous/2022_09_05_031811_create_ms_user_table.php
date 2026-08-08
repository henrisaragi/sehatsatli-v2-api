<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_user', function (Blueprint $table) {
            $table->integer('id')->primary()->comment("id user");
            $table->string('username')->comment("username");
            $table->string('password')->comment("password");
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('picture')->nullable()->comment("Foto Profil");
            $table->string('group')->nullable()->comment("User Level");
            $table->string('upt_id')->nullable()->comment("Kelompok UPT");
            $table->string('officer_id')->default('0')->comment("ID Petugas");
            $table->integer('status')->default(0);
            $table->integer('flag')->default(1);
            $table->string('trained')->default('1');
            $table->string('reset_key');
            $table->timestamp('modified')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_user');
    }
}
