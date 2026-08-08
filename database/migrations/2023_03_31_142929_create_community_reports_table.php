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
        Schema::create('community_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_laporan')->nullable();
            $table->string('name')->nullable();
            $table->date('report_date')->nullable();
            $table->text('description')->nullable();
            $table->integer('reporter_id')->nullable();
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
        Schema::dropIfExists('community_reports');
    }
};
