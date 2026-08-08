<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrForumTopicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_forum_topic', function (Blueprint $table) {
            $table->increments('id');
            $table->string('topic')->nullable()->comment("Judul / Topik");
            $table->text('discussion')->nullable()->comment("Diskusi / Pertanyaan");
            $table->string('posted_by')->nullable()->comment("Posting oleh");
            $table->string('hit')->nullable()->comment("Jumlah Dilihat");
            $table->dateTime('created')->nullable()->comment("Tanggal Dibuat");
            $table->timestamp('modified')->nullable()->useCurrent()->useCurrentOnUpdate()->comment("TimeStamp Data Ditambahkan");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tr_forum_topic');
    }
}
