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
        Schema::table('general_report_labs', function (Blueprint $table) {
            //
            $table->integer('final_result')->nullable()->after('updated_at');
            $table->integer('final_result_date')->nullable()->after('updated_at');
            $table->string('final_diagnosis_other')->nullable()->after('final_diagnosis');
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
