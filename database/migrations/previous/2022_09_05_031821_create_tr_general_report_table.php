<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrGeneralReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tr_general_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('report_code')->nullable()->comment("Kode Laporan");
            $table->string('case_found')->nullable()->comment("Yes - No");
            $table->date('report_date')->nullable()->comment("Tanggal Kejadian");
            $table->string('report_month')->nullable()->comment("Periode Bulan");
            $table->string('report_year')->nullable()->comment("Periode Tahun");
            $table->string('name')->nullable()->comment("Nama Pelapor");
            $table->string('gender')->nullable()->comment("Jenis Kelamin");
            $table->string('occupation')->nullable()->comment("Pekerjaan");
            $table->string('phone')->nullable()->comment("No. Telp/HP");
            $table->text('address')->nullable()->comment("Alamat");
            $table->string('upt_type')->nullable()->comment("Tipe UPT - TN/KSDA");
            $table->string('upt_name')->nullable()->comment("id UPT");
            $table->string('location_name')->nullable()->comment("Nama Lokasi");
            $table->string('conservation_type')->nullable()->comment("Jenis Lokasi Kejadian");
            $table->string('insitu_conservation')->nullable()->comment("Lembaga Konservasi InSitu");
            $table->string('insitu_other')->nullable()->comment("Insitu Lainnya");
            $table->string('exsitu_conservation')->nullable()->comment("Lembaga Konservasi Exsitu");
            $table->string('exsitu_other')->nullable()->comment("Exsitu Lainnya");
            $table->string('province')->nullable()->comment("Propinsi");
            $table->string('district')->nullable()->comment("Kab/Kota");
            $table->string('subdistrict')->nullable()->comment("Kecamatan");
            $table->string('village')->nullable()->comment("Kelurahan");
            $table->text('location_description')->nullable()->comment("Deskripsi Lokasi Kejadian");
            $table->string('coordinate')->nullable()->comment("Lintang");
            $table->string('sn_degree')->nullable()->comment("Derajat");
            $table->string('sn_minute')->nullable()->comment("Menit");
            $table->string('sn_second')->nullable()->comment("Detik");
            $table->string('east_degree')->nullable()->comment("Bujur Derajat");
            $table->string('east_minute')->nullable()->comment("Bujut Menit");
            $table->string('east_second')->nullable()->comment("Derajat Detik");
            $table->string('latitude')->nullable()->comment("Latitude");
            $table->string('longitude')->nullable()->comment("Longitude");
            $table->string('protected')->nullable()->comment("Satwa Dilindungi? Yes-No");
            $table->string('protected_animal')->nullable()->comment("id Satwa yang Dilindungi");
            $table->string('animal_name')->nullable()->comment("Nama Umum Satwa");
            $table->string('animal_latin_name')->nullable()->comment("Nama Latin Satwa");
            $table->string('animal_family')->nullable()->comment("Family Satwa");
            $table->string('animal_gender')->nullable()->comment("Jenis Kelamin Binatang");
            $table->string('animal_age')->nullable()->comment("Golongan Umur Satwa");
            $table->string('animal_live')->nullable()->comment("Jumlah Hidup");
            $table->string('animal_died')->nullable()->comment("Jumlah Mati");
            $table->string('animal_population')->nullable()->comment("Estimasi populasi beresiko");
            $table->string('dead_clinical_sign')->nullable()->comment("Tanda-tanda Klinis Hewan Mati");
            $table->string('other_dead_sign')->nullable()->comment("Tanda Mati Lainnya");
            $table->string('live_clinical_sign')->nullable()->comment("Tanda-tanda Klinis Hewan Hidup");
            $table->string('other_live_sign')->nullable()->comment("Tanda Hidup Lainnya");
            $table->text('chronological')->nullable()->comment("Kronologis Kejadian");
            $table->string('sample')->nullable()->comment("Yes-No");
            $table->string('case_type')->nullable()->comment("Non Penyakit / Penyakit");
            $table->string('nondisease')->nullable()->comment("Jelaskan Non Penyakit");
            $table->text('environment_description')->nullable()->comment("Keadaan Lingkungan");
            $table->string('follow_up')->nullable()->comment("tindak lanjut");
            $table->string('officer_follow_up')->nullable()->comment("Tindak Lanjut Lainnya");
            $table->string('reported_office')->nullable()->comment("Apakah sudah dilaporkan ke UPT KSDA/TN (Telp/SMS)");
            $table->text('additional_info')->nullable()->comment("Keterangan Tambahan");
            $table->date('officer_date_report')->nullable()->comment("Tanggal laporan Petugas");
            $table->string('temporary_diagnose')->nullable()->comment("Diagnosa Sementara");
            $table->string('case_status')->nullable()->comment("Status Kasus");
            $table->string('nondisease_drh')->nullable()->comment("Jelaskan Non Penyakit");
            $table->string('examination_method')->nullable()->comment("Metode Pemeriksaan");
            $table->string('temporary_diagnose_drh')->nullable()->comment("Diagnosa Sementara");
            $table->string('anthrax')->default('No')->comment("Ya - Tidak");
            $table->string('rabies')->default('No')->comment("Yes - No");
            $table->string('HPAI')->default('No')->comment("Ya - Tidak");
            $table->string('other_zoonosis')->nullable()->comment("Zoonosis lainnya");
            $table->string('reported_keswan')->nullable()->comment("Sudah dilaporkan ke Keswan - YES/ No");
            $table->string('reported_kesmas')->nullable()->comment("Sudah dilaporkan ke Keswan - Yes/No");
            $table->string('sample_by_drh')->nullable()->comment("Yes-No");
            $table->string('sample_lab')->nullable()->comment("Sample Dikirim ke Lab? Yes-No");
            $table->string('need_investigation')->nullable()->comment("Yes - No");
            $table->string('investigation_team')->nullable()->comment("Jenis Tim Investigasi");
            $table->string('reported_central')->nullable()->comment("Yes-No");
            $table->text('additional_info_drh')->nullable()->comment("Keterangan Tambahan - Drh");
            $table->date('drh_date')->nullable()->comment("Tanggal Drh");
            $table->string('drh_name')->nullable()->comment("Nama Dokter Hewan");
            $table->string('drh_occupation')->nullable()->comment("Jabatan Drh");
            $table->string('drh_sign')->nullable()->comment("TTD Drh - Yes/ No");
            $table->date('drh_date_2')->nullable()->comment("Tanggal Drh");
            $table->string('drh_name_2')->nullable()->comment("Nama Dokter Hewan");
            $table->string('drh_occupation_2')->nullable()->comment("Jabatan Drh");
            $table->string('drh_sign_2')->nullable()->comment("TTD Drh - Yes/ No");
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
            $table->text('final_diagnosis')->nullable()->comment("Diagnosis Akhir");
            $table->string('informed_keswankesmas')->nullable()->comment("Informasi Keswan Kesmas");
            $table->string('other_follow_up')->nullable()->comment("Tindak Lanjut Lainnya");
            $table->string('reporter_id')->nullable()->comment("ID Petugas Input");
            $table->string('officer_id')->nullable()->comment("ID Petugas Lapangan SMS");
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
        Schema::dropIfExists('tr_general_report');
    }
}
