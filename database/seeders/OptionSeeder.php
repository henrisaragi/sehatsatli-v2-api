<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OptionSeeder extends Seeder
{

  function moveFromTable($tableName, $slug)
  {
    $data = DB::table($tableName)->get('*');
    Option::create([
      // name & slug
      'name' => $slug,
      'slug' => Str::slug($slug),
      'value' =>  $data,
    ]);
    DB::statement("DROP TABLE " . $tableName);
  }

  function dropUnusedTables()
  {
    DB::statement("DROP TABLE ms_month");
    DB::statement("DROP TABLE ms_year");
  }

  function moveLocationTables()
  {

    DB::statement("INSERT INTO villages  (SELECT * FROM ms_village)");
    DB::statement("DROP TABLE ms_village");
    DB::statement("INSERT INTO sub_districts  (SELECT * FROM ms_sub_district)");
    DB::statement("DROP TABLE ms_sub_district");
    DB::statement("INSERT INTO districts  (SELECT * FROM ms_district)");
    DB::statement("DROP TABLE ms_district");
    DB::statement("INSERT INTO provinces  (SELECT * FROM ms_province)");
    DB::statement("DROP TABLE ms_province");
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    Option::truncate();
    $this->moveFromTable('ms_age_group', 'age_groups');
    $this->moveFromTable('ms_animal_condition', 'animal_conditions');
    $this->moveFromTable('ms_animal_gender', 'animal_genders');
    $this->moveFromTable('ms_case_type', 'case_types');
    $this->moveFromTable('ms_clinical_sign', 'clinical_signs');
    $this->moveFromTable('ms_conservation_institution', 'conservation_institutions');
    $this->moveFromTable('ms_conservation_type', 'conservation_types');
    $this->moveFromTable('ms_follow_up', 'follow_ups');
    $this->moveFromTable('ms_lab_result', 'lab_results');
    $this->moveFromTable('ms_location', 'locations');
    $this->moveFromTable('ms_location_type', 'location_types');
    $this->moveFromTable('ms_sample_time', 'sample_times');
    $this->moveFromTable('ms_team_category', 'team_categories');
    $this->moveFromTable('ms_user_status', 'statuses');
    $this->moveFromTable('ms_gender', 'genders');
    $this->moveFromTable('ms_sms_type', 'sms_types');
    $this->moveFromTable('ms_group', 'user_groups');
    $this->moveFromTable('ms_yesno', 'yesno');

    $this->dropUnusedTables();
    $this->moveLocationTables();


    $animal_types = [];
    $animal_type_db = DB::select("SELECT DISTINCT animal_type FROM ms_animal_name");
    foreach ($animal_type_db as $row) {
      array_push($animal_types, [
        'id' => $row->animal_type,
        'name' => $row->animal_type,
      ]);
    }
    Option::create([
      'name' => 'animal_types',
      'slug' => Str::slug('animal_types'),
      'value' =>  $animal_types,
    ]);

    Option::create([
      'name' => 'upt_type',
      'slug' => Str::slug('upt_type'),
      'value' => [
        ["id" => "PUSAT", "name" => "Pusat"],
        ["id" => "KSDA", "name" => "KSDA"],
        ["id" => "TN", "name" => "Taman Nasional"],
        ["id" => "LU", "name" => "Lembaga Umum"],
        ["id" => "LK", "name" => "Lembaga Khusus"],
      ],
    ]);
    Option::create([
      'name' => 'sample_results',
      'slug' => Str::slug('sample_results'),
      'value' => [
        ["id" => 1, "name" => "Positif"],
        ["id" => 2, "name" => "Negatif"],
        ["id" => 3, "name" => "Sample tidak dapat diuji"],
        ["id" => 4, "name" => "Tidak Jelas"],
        ["id" => 5, "name" => "Lainnya"],
      ],
    ]);

    Option::create([
      'name' => 'case_status',
      'slug' => Str::slug('case_status'),
      'value' => [
        ["id" => 1, "name" => "Bukan Suspect Penyakit"],
        ["id" => 2, "name" => "Suspect Penyakit"],
      ],
    ]);
    Option::create([
      'name' => 'sample_lab',
      'slug' => Str::slug('sample_lab'),
      'value' => [
        ["id" => 1, "name" => "Sampel yang diambil oleh dokter hewan"],
        ["id" => 2, "name" => "Sampel yang diambil oleh petugas lapangan"],
      ],
    ]);
    Option::create([
      'name' => 'investigation_team',
      'slug' => Str::slug('investigation_team'),
      'value' => [
        ["id" => 1, "name" => "Tim Investigasi Internal KLHK"],
        ["id" => 2, "name" => "Tim Investigasi Lintas Sektor"],
      ],
    ]);
    Option::create([
      'name' => 'species_category',
      'slug' => Str::slug('species_category'),
      'value' => [
        ["id" => 1, "name" => "Hewan"],
        ["id" => 2, "name" => "Tumbuhan"],
      ],
    ]);
    Option::create([
      'name' => 'verification_actions',
      'slug' => Str::slug('verification_actionsegory'),
      'value' => [
        ["id" => 1, "name" => "Menghubungi Keswan Setempat"],
        ["id" => 2, "name" => "Menghubungi Kesmas Setempat"],
        ["id" => 3, "name" => "Ambil Sampel Ulang"],
        ["id" => 4, "name" => "Bentuk tim investigasi lanjutan"],
        ["id" => 5, "name" => "Lapor ke pusat"],
      ],
    ]);
    Option::create([
      'name' => 'sample_templates',
      'slug' => Str::slug('sample_templates'),
      'value' => [
        ["id" => 1, "name" => "Form Template", 'template' => "Nama Lab: \nJenis Sampel: \nTipe Pengujian: \nKode Sampel: \nTanggal: \n Waktu Pengambilan Sampel: \n "],
      ],
    ]);
    Option::create([
      'name' => 'lab_actions',
      'slug' => Str::slug('lab_actionscategory'),
      'value' => [
        ["id" => 1, "name" => "Menginformasikan kepada petugas KESMAS dan KESWAN Setempat"],
      ],
    ]);
    Option::create([
      'name' => 'investigation_action',
      'slug' => Str::slug('investigation_action'),
      'value' => [
        ["id" => 1, "name" => "Melaporkan Yang Berwenang"],
      ],
    ]);
    
    Option::create([
        'name' => 'suspek_disease',
        'slug' => Str::slug('suspek_disease'),
        'value' => [
            ["id" => 0, "name" => "Bukan Suspect Penyakit"],
            ["id" => 1, "name" => "Suspect Penyakit"],
        ],
    ]);
    
    Option::create([
        'name' => 'protected',
        'slug' => Str::slug('protected'),
        'value' => [
            ["id" => 0, "name" => "Tidak Di Lindungi"],
            ["id" => 1, "name" => "Di Lindungi"],
        ],
    ]);
  }
}
