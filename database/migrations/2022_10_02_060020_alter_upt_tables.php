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
        //
        Schema::table('upts', function (Blueprint $table) {
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('subdistrict_id')->nullable();
            $table->integer('village_id')->nullable();
            $table->integer('conservation_type')->nullable();
            $table->json('insitu_conservation')->nullable();
            $table->string('insitu_other')->nullable();
            $table->json('exsitu_conservation')->nullable();
            $table->string('exsitu_other')->nullable();
            $table->json('upt_heads')->nullable();

            $table->dropColumn('province');
            $table->dropColumn('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
