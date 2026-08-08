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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);
            $table->string('user_id')->nullable();
            $table->string('month', 2)->nullable();
            $table->string('year', 4)->nullable();
            $table->boolean('send_report')->default(false);
            $table->integer('open_count')->nullable();
            $table->timestamp('last_seen')->nullable();

            $table->index(['id', 'status']);
            $table->index(['user_id', 'month', 'year']);
        });

        Schema::create('user_inboxes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);
            $table->string('user_id')->nullable();

            $table->timestamp('received_date');
            $table->timestamp('read_date');
            $table->string('subject')->nullable();
            $table->string('message', 500)->nullable();
            $table->boolean('read')->default(false);

            $table->index(['id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('user_inboxes');
    }
};
