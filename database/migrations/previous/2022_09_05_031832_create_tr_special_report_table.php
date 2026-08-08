<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrSpecialReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_special_report', function (Blueprint $table) {
            $table->increments('id')->comment("ID");
            $table->string('special_report_code')->nullable()->comment("Kode Laporan Khusus");
            $table->string('general_report_code')->nullable()->comment("Kode Laporan Umum");
            $table->date('report_date')->nullable()->comment("Tanggal Pengisian Formulir");
            $table->date('investigation_start')->nullable()->comment("Tanggal Mulai Investigasi");
            $table->date('investigation_end')->nullable()->comment("Tanggal Berakhir Investigasi");
            $table->string('animal_name')->nullable()->comment("Nama Umum");
            $table->string('animal_latin')->nullable()->comment("Nama Latin");
            $table->string('animal_family')->nullable()->comment("Nama Family");
            $table->string('animal_gender')->nullable()->comment("Jantan - Betina");
            $table->string('animal_age')->nullable()->comment("Umur Hewan");
            $table->string('animal_condition')->nullable()->comment("Kondisi Ditemukan - Hidup/Mati");
            $table->text('clinical_sign')->nullable()->comment("Tanda Klinis");
            $table->text('other_description')->nullable()->comment("Keterangan Lainnya");
            $table->string('case_type')->nullable()->comment("Non-Penyakit / Penyakit");
            $table->string('nondisease_detail')->nullable()->comment("Jelaskan - Non Penyakit");
            $table->text('diagnosis')->nullable()->comment("Diagnosis");
            $table->string('anthrax')->default('No')->comment("Ya - Tidak");
            $table->string('rabies')->default('No')->comment("Yes - No");
            $table->string('HPAI')->default('No')->comment("Ya - Tidak");
            $table->string('other_zoonosis')->nullable()->comment("Zoonosis lainnya");
            $table->string('reported_keswan')->nullable()->comment("Sudah dilaporkan ke Keswan - YES/ No");
            $table->string('reported_kesmas')->nullable()->comment("Sudah dilaporkan ke Keswan - Yes/No");
            $table->string('keswan_name')->nullable()->comment("Nama Keswan");
            $table->string('keswan_phone')->nullable()->comment("telepon keswan");
            $table->string('keswan_date')->nullable()->comment("Tanggal Melaporkan ke Keswan");
            $table->string('kesmas_name')->nullable()->comment("Nama Kesmas");
            $table->string('kesmas_phone')->nullable()->comment("Telepon Kesmas");
            $table->string('kesmas_date')->nullable()->comment("Tanggal Melaporkan Kesmas");
            $table->string('sample_lab')->nullable()->comment("Sample Dikirim ke Lab? Yes-No");
            $table->text('examination_method')->nullable()->comment("Metode Pemeriksaan");
            $table->string('lab_examination')->nullable()->comment("Ya - Tidak");
            $table->string('lab_result')->nullable()->comment("Hasil Lab");
            $table->text('solution')->nullable()->comment("Solusi / Tindakan");
            $table->string('need_police')->nullable()->comment("Ya - Tidak");
            $table->text('additional_description')->nullable()->comment("Keterangan Tambahan");
            $table->date('drh_date')->nullable()->comment("Tanggal Drh");
            $table->string('drh_name')->nullable()->comment("Nama Dokter Hewan");
            $table->string('drh_occupation')->nullable()->comment("Jabatan Drh");
            $table->string('drh_sign')->nullable()->comment("TTD Drh - Yes/ No");
            $table->date('drh_date_2')->nullable()->comment("Tanggal Drh");
            $table->string('drh_name_2')->nullable()->comment("Nama Dokter Hewan");
            $table->string('drh_occupation_2')->nullable()->comment("Jabatan Drh");
            $table->string('drh_sign_2')->nullable()->comment("TTD Drh - Yes/ No");
            $table->date('head_date')->nullable()->comment("Tanggal Kepala Team");
            $table->string('head_name')->nullable()->comment("Nama Kepala Team");
            $table->string('head_occupation')->nullable()->comment("Jabatan Kepala Team");
            $table->string('head_sign')->nullable()->comment("TTD Kepala Team- Yes/ No");
            $table->date('upt_head_date')->nullable()->comment("Tanggal Kepala Upt");
            $table->string('upt_head_name')->nullable()->comment("Nama Kepala UPT");
            $table->string('upt_head_occupation')->nullable()->comment("Jabatan Kepala UPT");
            $table->string('upt_head_sign')->nullable()->comment("TTD Kepala UPT - Yes/ No");
            $table->string('lab_anthrax')->default('No')->comment("Ya - Tidak");
            $table->string('lab_anthrax_result')->nullable()->comment("Hasil Lab Anthrax");
            $table->string('lab_anthrax_other')->nullable()->comment("Hasil Anthrax lainnya");
            $table->string('lab_rabies')->default('No')->comment("Yes - No");
            $table->string('lab_rabies_result')->nullable()->comment("Hasil Lab Rabies");
            $table->string('lab_rabies_other')->nullable()->comment("Hasil lainnya Rabies");
            $table->string('lab_HPAI')->default('No')->comment("Ya - Tidak");
            $table->string('lab_HPAI_result')->nullable()->comment("Hasil HPAI");
            $table->string('lab_HPAI_other')->nullable()->comment("Hasil Lab HPAI Lainnya");
            $table->string('informed_keswankesmas')->nullable()->comment("Informasi Keswan Kesmas");
            $table->string('other_follow_up')->nullable()->comment("Tindak Lanjut Lainnya");
            $table->text('patologi')->nullable()->comment("Data Patologi");
            $table->text('final_diagnosis')->nullable()->comment("Diagnosis Akhir");
            $table->string('reporter_id')->nullable()->comment("ID Petugas Input");
            $table->string('officer_id')->nullable()->comment("ID Petugas Lapangan SMS");
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
        Schema::dropIfExists('tr_special_report');
    }
}
