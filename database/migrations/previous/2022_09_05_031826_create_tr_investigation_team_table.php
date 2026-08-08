<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrInvestigationTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_investigation_team', function (Blueprint $table) {
            $table->increments('id')->comment("ID");
            $table->string('special_report')->nullable()->comment("Kode Laporan Khusus");
            $table->string('team_category')->nullable()->comment("Kategori Tim Investigasi");
            $table->string('name')->nullable()->comment("Nama");
            $table->string('institution')->nullable()->comment("nama Institusi");
            $table->string('position')->nullable()->comment("Jabatan");
            $table->string('phone')->nullable()->comment("Nomor HP");
            $table->dateTime('created')->nullable()->comment("Tanggal Dibuat");
            $table->timestamp('modified')->useCurrent()->useCurrentOnUpdate()->comment("Tanggal di modifikasi");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_investigation_team');
    }
}
