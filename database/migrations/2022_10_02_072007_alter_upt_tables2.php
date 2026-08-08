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
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });

        Schema::table('upts', function (Blueprint $table) {
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });

        Schema::table('general_report_locations', function (Blueprint $table) {
            $table->dropColumn('conservation_type');
            $table->dropColumn('insitu_other');
            $table->dropColumn('exsitu_other');
            $table->dropColumn('insitu_conservation');
            $table->dropColumn('exsitu_conservation');
            $table->dropColumn('province');
            $table->dropColumn('district');
            $table->dropColumn('subdistrict');
            $table->dropColumn('village');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });

        Schema::table('general_report_locations', function (Blueprint $table) {
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('subdistrict_id')->nullable();
            $table->integer('village_id')->nullable();
            $table->integer('conservation_type')->nullable();
            $table->json('insitu_conservation')->nullable();
            $table->string('insitu_other')->nullable();
            $table->json('exsitu_conservation')->nullable();
            $table->string('exsitu_other')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
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

        Schema::table('general_report_locations', function (Blueprint $table) {
            $table->dropColumn('conservation_type');
            $table->dropColumn('insitu_other');
            $table->dropColumn('exsitu_other');
            $table->dropColumn('insitu_conservation');
            $table->dropColumn('exsitu_conservation');
            $table->dropColumn('province_id');
            $table->dropColumn('district_id');
            $table->dropColumn('subdistrict_id');
            $table->dropColumn('village_id');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
        Schema::table('upts', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
