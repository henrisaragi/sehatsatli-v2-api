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
        Schema::create('general_report_sources', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('creator');
            $table->integer('updater');
            $table->boolean('training')->default(0);
            $table->integer('status')->default(1);
            $table->integer('user_id')->nullable();
            $table->integer('upt_id')->nullable();
            $table->string('report_code')->nullable();

            $table->date('report_date')->nullable();
            $table->string('location')->nullable()->comment("Nama Lokasi");
            $table->string('species_id')->nullable()->comment("Nama Spesies");
            $table->string('protected')->nullable()->comment("Yes - No");
            $table->string('species_name')->nullable()->comment("Nama Spesies");
            $table->string('dead')->nullable()->comment("Jumlah Mati");
            $table->string('live')->nullable()->comment("Jumlah Hidup");
            $table->string('description')->nullable()->comment("Keterangan");

            $table->index(['id', 'status']);
        });

        // Schema::create('general_report_public_source', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        //     $table->integer('status')->default(1);
        //     $table->string('report_code')->nullable();
        //     $table->date('report_date')->nullable();
        //     $table->string('name');
        //     $table->string('phone');
        //     $table->string('location')->nullable()->comment("Nama Lokasi");
        //     $table->string('species_id')->nullable()->comment("Nama Spesies");
        //     $table->string('protected')->nullable()->comment("Yes - No");
        //     $table->string('species_name')->nullable()->comment("Nama Spesies");
        //     $table->string('dead')->nullable()->comment("Jumlah Mati");
        //     $table->string('live')->nullable()->comment("Jumlah Hidup");
        //     $table->string('description')->nullable()->comment("Keterangan");
        // });

        Schema::create('general_report_reporters', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->string('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->string('occupation')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('case_found')->default(true);
            $table->json('additional_reporters')->nullable();
            $table->json('acknowledged_by')->nullable();
            //
        });

        Schema::create('general_report_locations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->integer('upt_id')->nullable();
            $table->string('upt_type')->nullable();
            $table->string('upt_name')->nullable();
            $table->string('location_name')->nullable();
            $table->string('conservation_type')->nullable();
            $table->string('insitu_conservation')->nullable();
            $table->string('insitu_other')->nullable();
            $table->string('exsitu_conservation')->nullable();
            $table->string('exsitu_other')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('village')->nullable();
            $table->text('location_description')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });

        Schema::create('general_report_species', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->string('category')->nullable();
            $table->string('protected')->nullable();
            $table->string('protected_species')->nullable();

            $table->string('species_name')->nullable();
            $table->string('species_latin_name')->nullable();
            $table->string('species_family')->nullable();

            // $table->string('species_gender')->nullable();
            $table->integer('species_age')->nullable();
            $table->integer('population')->nullable();
        });

        Schema::create('general_report_diagnoses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->date('report_date')->nullable();
            $table->integer('dead')->nullable();
            $table->json('dead_sign')->nullable();
            $table->text('dead_sign_other')->nullable();
            $table->integer('live')->nullable();
            $table->json('live_sign')->nullable();
            $table->text('live_sign_other')->nullable();
            $table->text('chronological')->nullable();
            $table->text('sampling')->nullable();
            // $table->string('case_type')->nullable();
            // $table->string('nondisease')->nullable();
            // $table->text('environment_description')->nullable();
            $table->text('follow_up')->nullable();
            $table->integer('temporary_diagnosis_id')->nullable();
            $table->text('diagnosis')->nullable();
            // $table->integer('temporary_disease_id')->nullable();
        });

        Schema::create('general_report_doctor_verifications', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->date('verified_date')->nullable();
            // $table->string('doctor_name')->nullable();
            // $table->string('doctor_occupation')->nullable();
            $table->boolean('verified')->default(0);
            $table->text('verification')->nullable();
            $table->text('sampling')->nullable();
            $table->integer('temporary_disease_id')->nullable();
            $table->text('action')->nullable();
            $table->text('doctor_information')->nullable();
            $table->json('involved_doctors')->nullable();
        });

        Schema::create('general_report_acknowledgements', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->date('upt_head_date')->nullable();
            $table->string('upt_head_name')->nullable();
            $table->string('upt_head_occupation')->nullable();
            $table->string('upt_head_sign')->nullable();
        });

        Schema::create('general_report_labs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->timestamps();
            $table->integer('final_disease_id')->nullable();
            $table->text('final_diagnosis')->nullable();
            $table->string('follow_up')->nullable();
        });

        // Schema::create('general_report_special', function (Blueprint $table) {

        //     $table->integer('id')->primary();
        //     $table->timestamps();
        //     $table->foreignId('general_report_id')->constrained('general_report_source')->onDelete('cascade');
        //     // $table->integer('general_report_id');
        //     // $table->foreign('general_report_id')->references('id')->on('general_report_source');
        //     $table->json('photo')->nullable();
        //     $table->text('solution')->nullable();
        //     $table->string('need_police')->nullable();
        //     $table->text('additional_description')->nullable();

        //     $table->date('drh_date')->nullable();
        //     $table->string('drh_name')->nullable();
        //     $table->string('drh_occupation')->nullable();
        //     $table->string('drh_sign')->nullable();
        //     $table->date('drh_date2')->nullable();
        //     $table->string('drh_name2')->nullable();
        //     $table->string('drh_occupation2')->nullable();
        //     $table->string('drh_sign2')->nullable();

        //     $table->date('head_date')->nullable();
        //     $table->string('head_name')->nullable();
        //     $table->string('head_occupation')->nullable();
        //     $table->string('head_sign')->nullable();

        //     $table->date('upt_head_date')->nullable();
        //     $table->string('upt_head_name')->nullable();
        //     $table->string('upt_head_occupation')->nullable();
        //     $table->string('upt_head_sign')->nullable();


        //     $table->timestamp('modified')->nullable();
        //     $table->timestamp('created')->nullable();
        //     $table->integer('status');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('general_report_sources');
        //Schema::dropIfExists('general_report_public_source');
        Schema::dropIfExists('general_report_reporters');
        Schema::dropIfExists('general_report_locations');
        Schema::dropIfExists('general_report_species');
        Schema::dropIfExists('general_report_diagnoses');
        Schema::dropIfExists('general_report_doctor_verifications');
        Schema::dropIfExists('general_report_acknowledgements');
        Schema::dropIfExists('general_report_labs');
        //Schema::dropIfExists('general_report_special');
    }
};
