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
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);

            $table->integer('category')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('latin_name')->nullable();
            $table->string('type')->nullable();
            $table->string('family')->nullable();
            $table->boolean('priority')->default(0);
            $table->boolean('endangered')->default(1);
            $table->boolean('protected')->default(1);

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
        Schema::dropIfExists('species');
    }
};
