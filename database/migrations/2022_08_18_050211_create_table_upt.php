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
        Schema::create('upts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);

            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable()->comment("PUSAT/TN/KSDA/LKU/LKS");
            $table->string('upt_type')->nullable()->comment("Tipe UPT");
            $table->integer('unit_id')->nullable(); // Parent
            $table->string('address')->nullable(); // 
            $table->string('province')->nullable();
            $table->string('location')->nullable()->comment("Lokasi Balai");
            $table->string('latitude')->nullable()->comment("Latitude");
            $table->string('longitude')->nullable()->comment("Longitude");
            $table->string('upload_photo')->nullable();

            $table->index(['id', 'status']);
        });

        Schema::create('upt_species', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->integer('status')->default(1);
            $table->integer('upt_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('species_id')->nullable();
            $table->integer('population')->nullable();

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
        Schema::dropIfExists('upts');
        Schema::dropIfExists('upt_species');
    }
};
