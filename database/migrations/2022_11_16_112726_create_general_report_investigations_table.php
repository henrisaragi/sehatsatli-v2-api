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
        Schema::create('general_report_investigations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->date('investigation_date_from')->nullable();
            $table->date('investigation_date_to')->nullable();
            $table->text('inspection_method')->nullable();
            $table->json('evidence')->nullable();
            $table->text('follow_up_carried_out')->nullable();
            $table->text('additional_information')->nullable();
            $table->json('data_investigation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_report_investigations');
    }
};
