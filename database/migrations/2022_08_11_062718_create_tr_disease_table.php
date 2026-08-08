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
        // -description -> text
        // -symptom
        // -treatment
        // -priority -> radio button
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->text('symptom')->nullable();
            $table->json('symptom_details')->nullable();
            $table->text('treatment')->nullable();
            $table->json('treatment_details')->nullable();
            $table->boolean('priority')->default(0);
            $table->boolean('zoonosis')->default(0);
            //$table->string('upload_photo')->nullable();

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
        Schema::dropIfExists('diseases');
    }
};
