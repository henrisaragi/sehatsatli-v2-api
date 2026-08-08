<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrSmsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_sms_log', function (Blueprint $table) {
            $table->string('msisdn', 20)->nullable();
            $table->string('msg', 500)->nullable();
            $table->date('created_at');
            $table->bigInteger('id')->primary();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_sms_log');
    }
}
