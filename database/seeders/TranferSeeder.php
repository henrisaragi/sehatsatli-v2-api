<?php

namespace Database\Seeders;

// use App\Models\GeneralReport;
// use App\Models\GeneralReportAnimal;
// use App\Models\GeneralReportLocation;
// use App\Models\GeneralReportSample;
// use App\Models\GeneralReportDoctor;
// use App\Models\GeneralReportUpt;
// use App\Models\GeneralReportLab;
// use App\Models\GeneralReportSpecial;
use App\Models\Disease;
use App\Models\GeneralReportAcknowledgement;
use App\Models\GeneralReportDiagnosis;
use App\Models\GeneralReportLab;
use App\Models\GeneralReportReporter;
use App\Models\GeneralReportSource;
use App\Models\GeneralReportLocation;
use App\Models\GeneralReportSpecies;
use App\Models\GeneralReportVerification;
use App\Models\GeneralReportInvestigation;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranferSeeder extends Seeder
{
    private function createSample($general_report_sample)
    {
        $sampling = "";
        if ($general_report_sample->lab_name) {
            $sampling .= "Nama Lab: " . $general_report_sample->lab_name . "\n";
        }
        if ($general_report_sample->test_type) {
            $sampling .= "Tipe Pengujian: " . $general_report_sample->test_type . "\n";
        }
        if ($general_report_sample->sample_code) {
            $sampling .= "Kode Sample: " . $general_report_sample->sample_code . "\n";
        }
        if ($general_report_sample->sample_date) {
            $sampling .= "Tanggal: " . $general_report_sample->sample_date . "\n";
        }
        if ($general_report_sample->sample_date) {
            $sampling .= "Waktu Pengambilan Sampel: " . $general_report_sample->sample_time . "\n";
        }
        return $sampling;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $case_types = [
                "NP" => 1,
                "P" => 2,
                "BSD" => 3,
                "SP" => 4,
                "TAK" => 5,
            ];

            $genders = [
                "L" => 1,
                "P" => 2,
            ];

            $converation_types = [
                "IS" => 1,
                "ES" => 2,
            ];

            $age_groups = [
                "TidakDiketahui" => 1,
                "Anakan" => 2,
                "Remaja" => 3,
                "Dewasa" => 4
            ];

            $general_reports = DB::table('tr_general_report')->get('*');
            $smses = collect(DB::table('tr_sms')->get('*'));
            $general_report_samples = DB::table('tr_general_report_sample')->get('*');
            $options = DB::table('options')->get('*');
            $general_report_officers = DB::table('tr_general_report_officer')->get('*');
            $general_report_results = DB::table('tr_general_report_result')->get('*');
            $special_reports = DB::table('tr_special_report')->get('*');
            $special_report_samples = DB::table('tr_special_report_sample')->get('*');

            foreach ($general_reports as $general_report) {
                $follow_ups = $options->firstWhere('slug', "follow-ups");
                $sms = $smses->firstWhere('general_report_code', $general_report->report_code);
                if (!$sms) {
                    continue; // not valid
                }

                if ($general_report->case_found != 'Yes' || $sms->case_type == 'tak') {

                    $bulan = strlen($general_report->report_month);
                    $data = [
                        'user_id' => $sms->reporter_id,
                        'month' => $bulan == 1 ? '0' . $general_report->report_month : $general_report->report_month,
                        'year' => $general_report->report_year,
                        'last_seen' => $general_report->report_date,
                        'send_report' => true,
                    ];
                    UserActivity::create($data);

                    continue; // skip lanjutkan looping
                }

                GeneralReportSource::create([
                    'id' => $general_report->id,
                    'created_at' => $general_report->created,
                    'updated_at' => $general_report->modified,
                    'creator' => $general_report->reporter_id ?? 1,
                    'updater' => $general_report->officer_id ?? 1,
                    'training' => false,
                    'user_id' => $general_report->reporter_id  ?? 1, //tidak bisa null
                    'upt_id' => $general_report->upt_name ?? null,
                    'report_code' => $general_report->report_code  ?? null,
                    'report_date' => $general_report->report_date == null ?  $general_report->created  : $general_report->report_date,
                    'location' => $general_report->location_name  ?? null,
                    'species_id' => $general_report->protected_animal ?? null, // didalam sql tidak ada
                    'protected' => $general_report->protected  ?? null,
                    'species_name' => $general_report->animal_name  ?? null,
                    'dead' => $general_report->animal_died  ?? null,
                    'live' => $general_report->animal_live  ?? null,
                    'description' => $sms->description,
                    // ...
                ]);

                $user = null;
                if ($general_report->reporter_id) {
                    $user = User::with('upt')->where('id', $general_report->officer_id ?? $general_report->reporter_id)->first();
                }

                if ($user) {

                    // TODO: Enrique
                    // 1 - Additional reporter
                    // Load ke tr_general_report_officer by $general_report id,
                    // jika ditemukan simpan sebagai additional_reporters 
                    // field: name, occupation, phone, signed(boolean)

                    $additional_officers = [];
                    $officers = $general_report_officers->where('general_report', '==', $general_report->id);
                    foreach ($officers as $officer) {
                        $signed = 0;
                        if ($officer->signed == "Yes") {
                            $signed = 1;
                        }
                        $array_tambahan = [
                            'name' => $officer->name,
                            'occupation' => $officer->occupation,
                            'phone' => $officer->phone,
                            'signed' => $signed,
                        ];
                        array_push($additional_officers, $array_tambahan);
                    }


                    // 2 - Acknowledged by
                    // Pindahkan yang bagian bawah, general report acknowledgement
                    // masukkan ke field acknowledged_by sbg array
                    // field date, name, occupation, signed(boolean)
                    $acknowledged_by = [
                        'upt_head_date' => $general_report->upt_head_date ?? null,
                        'upt_head_name' => $general_report->upt_head_name ?? null,
                        'upt_head_occupation' => $general_report->upt_head_occupation ?? null,
                        'upt_head_sign' => true,
                    ];

                    GeneralReportReporter::create([
                        'created_at' => $general_report->created,
                        'id' => $general_report->id,
                        'user_id' => $general_report->reporter_id ?? null,
                        'name' => $general_report->name ?? $user->name,
                        'gender' => $genders[$general_report->gender] ?? $user->gender,
                        'occupation' => $general_report->occupation ?? $user->occupation,
                        'phone' => $general_report->phone ?? $user->phone,
                        'address' => $general_report->address ?? $user->upt->address ?? null,
                        'case_found' => $general_report->case_found ?? null,
                        'created_at' => $general_report->created,
                        'updated_at' => $general_report->modified,
                        'additional_reporters' => $additional_officers ?? null,
                        'acknowledged_by' => $acknowledged_by ?? null,
                        //'report_date' => $general_report->report_date ?? null,
                        //'upt_id' => $general_report->upt_name ?? null,
                    ]);
                }

                if ($general_report->upt_type) {

                    $insitu_conservation = null;
                    if ($general_report->insitu_conservation) {
                        $decoded = json_decode("[" . $general_report->insitu_conservation . "]");
                        $insitu_conservation = [];
                        foreach ($decoded as $value) {
                            $insitu_conservation[$value] = true;
                        }
                    }

                    $exsitu_conservation = null;
                    if ($general_report->exsitu_conservation) {
                        $decoded = json_decode("[" . $general_report->exsitu_conservation . "]");
                        $exsitu_conservation = [];
                        foreach ($decoded as $value) {
                            $exsitu_conservation[$value] = true;
                        }
                    }


                    GeneralReportLocation::create([
                        'id' => $general_report->id,
                        'upt_id' => $general_report->upt_name ?? null,
                        'upt_type' => $general_report->upt_type ?? null,
                        'upt_name' => $general_report->upt_name ?? null,
                        'location_name' => $general_report->location_name ?? null,
                        'conservation_type' => $general_report->conservation_type ? $converation_types[$general_report->conservation_type] : null,
                        'insitu_conservation' => $insitu_conservation,
                        'insitu_other' => $general_report->insitu_other ?? null,
                        'exsitu_conservation' => $exsitu_conservation,
                        'exsitu_other' => $general_report->exsitu_other ?? null,
                        'province_id' => $general_report->province ?? null,
                        'district_id' => $general_report->district ?? null,
                        'subdistrict_id' => $general_report->subdistrict,
                        'village_id' => $general_report->village,
                        'location_description' => $general_report->location_description ?? null,
                        'latitude' => $general_report->latitude ?? null,
                        'longitude' => $general_report->longitude ?? null,
                        'created_at' => $general_report->created,
                        'updated_at' => $general_report->modified,
                    ]);
                }

                // if ($general_report->upt_head_name) {
                //     GeneralReportAcknowledgement::create([
                //         'id' => $general_report->id,
                //         'upt_head_date' => $general_report->upt_head_date ?? null,
                //         'upt_head_name' => $general_report->upt_head_name ?? null,
                //         'upt_head_occupation' => $general_report->upt_head_occupation ?? null,
                //         'upt_head_sign' => true,
                //     ]);
                // }

                if ($general_report->animal_name) {
                    GeneralReportSpecies::create([
                        'id' => $general_report->id ?? null,
                        // 'category' => $general_report->id,//di dalam sql tidak ada
                        'protected' => $general_report->protected == "Yes",
                        'protected_species' => $general_report->protected_animal ?? null,
                        'species_name' => $general_report->animal_name ?? null,
                        'species_latin_name' => $general_report->animal_latin_name ?? null,
                        'species_family' => $general_report->animal_family ?? null,
                        //'species_gender' => $general_report->animal_gender ?? null,
                        'species_age' => $general_report->animal_age ? $age_groups[$general_report->animal_age] : null,
                        'population' => $general_report->animal_population ?? null,
                        'created_at' => $general_report->created,
                        'updated_at' => $general_report->modified,
                    ]);
                }

                if (
                    $general_report->animal_died
                    || $general_report->animal_live
                    || $general_report->chronological
                    || $general_report->temporary_diagnose
                ) {

                    $general_report_sample = $general_report_samples
                        ->where("general_report", $general_report->id)
                        ->where('flag', 'officer');
                    $sampling = null;
                    if ($general_report_sample) {
                        $sampling = "";
                        foreach ($general_report_sample as $sample) {
                            $sampling .= $this->createSample($sample);
                        }
                    }

                    // follow up :
                    // Data follow up, query ke tabel Options dengan name follow_ups, digabung berdasar nilai di general_report.follow_up gabungkan dlm 1 string
                    // jika general_report.officer_follow_up tambahkan "Lapor ke UPT KSDA/TN (Telp/SMS)"
                    $follow_up_data = "";
                    $follow_ups = json_decode($follow_ups->value, true);
                    if ($general_report->follow_up) {
                        $ids = explode(',', $general_report->follow_up);
                        foreach ($ids as $id) {
                            foreach ($follow_ups as $follow_up) {
                                if ($id == $follow_up['id']) {
                                    $follow_up_data = $follow_up_data  . "- " . $follow_up['name'] . "\n";
                                }
                            }
                        }
                    }
                    if ($general_report->officer_follow_up) {
                        $follow_up_data = $follow_up_data  . "- " . $general_report->officer_follow_up . "\n";
                    }
                    if ($general_report->reported_office == "Yes" || $general_report->reported_office == 1) {
                        $follow_up_data = $follow_up_data  . "- Sudah dilaporkan ke UPT KSDA/TN (Telp/SMS)" . "\n";
                    }

                    $generalreportdiagnosis = new  GeneralReportDiagnosis();
                    $generalreportdiagnosis->id = $general_report->id;
                    $generalreportdiagnosis->report_date = $general_report->report_date == null ?  $general_report->created  : $general_report->report_date;
                    $generalreportdiagnosis->dead = $general_report->animal_died ?? 0;
                    $generalreportdiagnosis->dead_sign = $general_report->dead_clinical_sign ? json_decode("[" . $general_report->dead_clinical_sign . "]") : null;
                    $generalreportdiagnosis->dead_sign_other = $general_report->other_dead_sign;
                    $generalreportdiagnosis->live = $general_report->animal_live ?? 0;
                    $generalreportdiagnosis->live_sign = $general_report->live_clinical_sign ? json_decode("[" . $general_report->live_clinical_sign . "]") : null;
                    $generalreportdiagnosis->live_sign_other = $general_report->other_live_sign;
                    $generalreportdiagnosis->chronological = $general_report->chronological ?? null;
                    $generalreportdiagnosis->sampling = $sampling;
                    $generalreportdiagnosis->follow_up = $follow_up_data;
                    $generalreportdiagnosis->diagnosis = $general_report->additional_info ?? null; // dari temp_diagnose atau dari nondisease;
                    $generalreportdiagnosis->created_at = $general_report->created;
                    $generalreportdiagnosis->updated_at = $general_report->modified;
                    $generalreportdiagnosis->temporary_diagnosis_id = $case_types[$general_report->case_type ?? "NP"];
                    $generalreportdiagnosis->save();
                }

                $hasDiagnosis = true;
                if (
                    $general_report->case_status == null &&
                    $general_report->nondisease_drh == null &&
                    $general_report->temporary_diagnose_drh == null &&
                    $general_report->anthrax == null &&
                    $general_report->rabies == null &&
                    $general_report->HPAI == null &&
                    $general_report->other_zoonosis == null &&
                    $general_report->reported_keswan == null &&
                    $general_report->reported_kesmas == null &&
                    $general_report->sample_by_drh == null &&
                    $general_report->sample_lab == null &&
                    $general_report->need_investigation == null &&
                    $general_report->drh_date == null &&
                    $general_report->drh_name == null &&
                    $general_report->drh_occupation == null &&
                    $general_report->drh_sign == null &&
                    $general_report->drh_date_2 == null &&
                    $general_report->drh_name_2 == null &&
                    $general_report->drh_occupation_2 == null &&
                    $general_report->drh_sign_2 == null
                ) {

                    $hasDiagnosis = false;
                }

                if ($hasDiagnosis) {
                    $additional_info_drh = [];
                    $verified_status = false;
                    $verification = null;
                    $temporary_disease_id = 1;
                    $action = null;
                    if ($general_report->case_status == "SP") {
                        $verified_status = true;
                    }
                    if ($general_report->nondisease_drh != null) {
                        $verification = $verification . $general_report->nondisease_drh;
                    } else if ($general_report->temporary_diagnose_drh != null) {
                        $verification = $verification . $general_report->temporary_diagnose_drh;
                    }
                    if ($general_report->anthrax == "Yes") {
                        $temporary_disease_id = 4;
                    } else if ($general_report->rabies == "Yes") {
                        $temporary_disease_id = 3;
                    } else if ($general_report->HPAI == "Yes") {
                        $temporary_disease_id = 5;
                    }
                    $sampling = null;

                    $general_report_sample = $general_report_samples
                        ->where("general_report", $general_report->id)
                        ->where('flag', 'drh');

                    $sampling = null;
                    if ($general_report_sample) {
                        $sampling = "";
                        foreach ($general_report_sample as $sample) {
                            $sampling .= $this->createSample($sample);
                        }
                    }

                    if (
                        $general_report->drh_date != null ||
                        $general_report->drh_date != "" ||
                        $general_report->drh_name != null ||
                        $general_report->drh_name != "" ||
                        $general_report->drh_occupation != null ||
                        $general_report->drh_occupation != ""

                    ) {
                        $sign = 0;
                        if ($general_report->drh_sign == "Yes") {
                            $sign = 1;
                        }
                        $array_tambahan = [
                            'date' => $general_report->drh_date,
                            'name' => $general_report->drh_name,
                            'occupation' => $general_report->drh_occupation,
                            'sign' => $sign,
                        ];
                        array_push($additional_info_drh, $array_tambahan);
                    }
                    if (
                        $general_report->drh_date_2 != null ||
                        $general_report->drh_date_2 != "" ||
                        $general_report->drh_name_2 != null ||
                        $general_report->drh_name_2 != "" ||
                        $general_report->drh_occupation_2 != null ||
                        $general_report->drh_occupation_2 != ""
                    ) {
                        $sign = 0;
                        if ($general_report->drh_sign_2 == "Yes") {
                            $sign = 1;
                        }
                        $array_tambahan = [
                            'date' => $general_report->drh_date_2,
                            'name' => $general_report->drh_name_2,
                            'occupation' => $general_report->drh_occupation_2,
                            'sign' => $sign,
                        ];
                        array_push($additional_info_drh, $array_tambahan);
                    }
                    if ($general_report->other_zoonosis != null) {
                        $action = $action . "Zoonosis Lain, " . $general_report->other_zoonosis . "\n";
                    }
                    if ($general_report->reported_keswan != null && ($general_report->reported_keswan == "Yes" || $general_report->reported_keswan == 1)) {
                        $action = $action . "- Di informasikan kepada petugas keswan\n";
                    }
                    if ($general_report->reported_kesmas != null && ($general_report->reported_kesmas == "Yes" || $general_report->reported_kesmas == 1)) {
                        $action = $action . "- Di informasikan kepada petugas kesmas\n";
                    }
                    if ($general_report->need_investigation != null && ($general_report->need_investigation == "Yes" || $general_report->need_investigation == 1)) {
                        $action = $action . "- Perlu investigasi lebih lanjut\n";
                    }
                    if ($general_report->investigation_team != null && ($general_report->investigation_team == "Yes" && $general_report->investigation_team == 1)) {
                        $action = $action . "- Dibentuk tim investigasi\n";
                    }
                    if ($general_report->reported_central != null && ($general_report->reported_central == "Yes" || $general_report->reported_central == 1)) {
                        $action = $action . "- Sudah dilaporkan ke pusat\n";
                    }
                    if ($general_report->sample_by_drh != null && ($general_report->sample_by_drh == "Yes" || $general_report->sample_by_drh == 1)) {
                        $action = $action . "- Pengambilan Sampel Ulang Oleh Dokter\n";
                    }
                    if ($general_report->sample_lab != null && ($general_report->sample_lab == "Yes"  || $general_report->sample_lab == 1)) {
                        $action = $action . "- Pengiriman Sampel ke Lab:\n";
                    }
                    GeneralReportVerification::create([
                        "id" => $general_report->id,
                        'created_at' => $general_report->created,
                        'updated_at' => $general_report->modified,
                        'verified_date' => $general_report->drh_date,
                        'verified' => $verified_status,
                        'verification' => $verification,
                        'temporary_disease_id' => $temporary_disease_id,
                        'sampling' => $sampling,
                        'action' => $action,
                        'involved_doctors' => $additional_info_drh,
                    ]);
                }

                $hasLab = false;

                if ($hasDiagnosis) {
                    if (
                        $general_report->lab_anthrax == "Yes" || $general_report->lab_anthrax == 1
                        || $general_report->lab_rabies == "Yes" || $general_report->lab_rabies == 1
                        || $general_report->lab_HPAI == "Yes" || $general_report->lab_HPAI == 1
                        || $general_report->informed_keswankesmas == "Yes" || $general_report->informed_keswankesmas == 1
                        || $general_report->other_follow_up != null || $general_report->other_follow_up != ""
                        || $general_report->final_diagnosis != null || $general_report->final_diagnosis != ""
                    ) {
                        $hasLab = true;
                    }

                    if ($hasLab) {
                        $final_disease_id = 1;
                        if ($general_report->lab_anthrax == "Yes") {
                            $temporary_disease_id = 4;
                        } else if ($general_report->lab_rabies == "Yes") {
                            $temporary_disease_id = 3;
                        } else if ($general_report->lab_HPAI == "Yes") {
                            $temporary_disease_id = 5;
                        }


                        $lab_follow_up = '';
                        if ($general_report->informed_keswankesmas == "Yes" || $general_report->informed_keswankesmas == 1) {
                            $lab_follow_up = "- Menginformasikan kepada petugas KESMAS dan KESWAN Setempat\n";
                        }

                        if ($general_report->other_follow_up != null || $general_report->other_follow_up != "") {
                            $lab_follow_up .= "- " . $general_report->other_follow_up . "\n";
                        }

                        // find in general_report_results 
                        $general_report_result = $general_report_results->where('general_report', $general_report->id)->first();

                        $report_lab_data = [
                            "id" => $general_report->id,
                            'final_result_date' => $general_report->report_date == null ?  $general_report->created  : $general_report->report_date,
                            'final_disease_id' => $final_disease_id,
                            'final_diagnosis' => $general_report->final_diagnosis ?? null,
                            'follow_up' => $lab_follow_up,
                            'created_at' => $general_report->created,
                            'updated_at' => $general_report->modified,
                        ];

                        if ($general_report_result) {
                            $report_lab_data['final_diagnosis_other'] = $general_report_result->name . " - " . $general_report_result->result;
                        }
                        GeneralReportLab::create($report_lab_data);
                    }
                }

                // find spcial report by general report code
                $special_report = $special_reports->where('general_report_code', $general_report->report_code)->first();

                // create GeneralReportInvestigation based on special report
                if ($special_report) {

                    // check in special report for these fields: clinical_sign, other_description, nondisease_detail, diagnosis, examination_method
                    // combine them into inspection_method
                    $inspection_method = "";
                    if ($special_report->diagnosis) {
                        $inspection_method .= "Diagnosis: " . $special_report->diagnosis . "\n";
                    }
                    if ($special_report->examination_method) {
                        $inspection_method .= "Metode Pemeriksaan: " . $special_report->examination_method . "\n";
                    }
                    if ($special_report->clinical_sign) {
                        $inspection_method .= "Tanda Klinis: " . $special_report->clinical_sign . "\n";
                    }
                    if ($special_report->other_description) {
                        $inspection_method .= "Deskripsi Lain: " . $special_report->other_description . "\n";
                    }
                    if ($special_report->nondisease_detail) {
                        $inspection_method .= "Detail Non Penyakit: " . $special_report->nondisease_detail . "\n";
                    }

                    $data_investigation = [];

                    if ($special_report->drh_name) {
                        $sign = 0;
                        if ($special_report->drh_sign == "Yes" || $special_report->drh_sign == 1) {
                            $sign = 1;
                        }
                        $array_tambahan = [
                            'date' => $special_report->drh_date,
                            'name' => $special_report->drh_name,
                            'occupation' => $special_report->drh_occupation,
                            'sign' => $sign,
                        ];
                        array_push($data_investigation, $array_tambahan);
                    }

                    if ($special_report->drh_name_2) {
                        $sign = 0;
                        if ($special_report->drh_sign_2 == "Yes" || $special_report->drh_sign_2 == 1) {
                            $sign = 1;
                        }
                        $array_tambahan = [
                            'date' => $special_report->drh_date_2,
                            'name' => $special_report->drh_name_2,
                            'occupation' => $special_report->drh_occupation_2,
                            'sign' => $sign,
                        ];
                        array_push($data_investigation, $array_tambahan);
                    }

                    if ($special_report->upt_head_name) {
                        $sign = 0;
                        if ($special_report->drh_sign_2 == "Yes" || $special_report->drh_sign_2 == 1) {
                            $sign = 1;
                        }
                        $acknowledged_by = [
                            'date' => $special_report->upt_head_date,
                            'name' => $special_report->upt_head_name,
                            'occupation' => $special_report->upt_head_occupation,
                            'sign' => $sign,
                        ];
                        array_push($data_investigation, $array_tambahan);
                    }

                    // find samples in special_report_samples by special_report id
                    $special_report_sample = $special_report_samples->where('special_report', $special_report->id);

                    $samples = null;
                    if ($special_report_sample) {
                        $samples = [];
                        foreach ($special_report_sample as $sample) {
                            $sampling = $this->createSample($sample);
                            array_push($samples, $sampling);
                        }
                    }


                    GeneralReportInvestigation::create([
                        'id' => $general_report->id,
                        'investigation_date_from' => $special_report->investigation_start,
                        'investigation_date_to' => $special_report->investigation_end,
                        'inspection_method' => $inspection_method,
                        'evidence' => $samples,
                        'additional_information' => $special_report->final_diagnosis,
                        'follow_up_carried_out' => $special_report->solution,
                        'data_investigation' => $data_investigation,
                    ]);
                }
            }

            //     DB::rollBack();
        });

        // DB::statement("DROP TABLE tr_general_report");
        // DB::statement("DROP TABLE tr_sms");
        // DB::statement("DROP TABLE tr_general_report_sample");
        // DB::statement("DROP TABLE tr_general_report_officer");
        // DB::statement("DROP TABLE tr_investigation_team");
        // DB::statement("DROP TABLE tr_evidence");
        // DB::statement("DROP TABLE tr_forum_message");
        // DB::statement("DROP TABLE tr_forum_reply");
        // DB::statement("DROP TABLE tr_forum_topic");
        // DB::statement("DROP TABLE tr_general_report_animal");
        // DB::statement("DROP TABLE tr_log");
        // DB::statement("DROP TABLE tr_manual_report");
        // DB::statement("DROP TABLE tr_news");
        // DB::statement("DROP TABLE tr_newuser");
        // DB::statement("DROP TABLE tr_sms_log");
        // DB::statement("DROP TABLE tr_sms_queue");
    }
}
