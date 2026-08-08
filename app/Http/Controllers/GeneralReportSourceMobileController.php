<?php

namespace App\Http\Controllers;

use App\Models\GeneralReportSource;
use App\Models\GeneralReportLocation;
use App\Models\GeneralReportSpecies;
use App\Models\UserActivity;
use App\Models\User;
use App\Models\Species;
use App\Models\UPT;
use App\Models\GeneralReportReporter;
use App\Models\GeneralReportDiagnosis;
use App\Models\GeneralReportAcknowledgement;
use App\Models\GeneralReportVerification;
use App\Models\GeneralReportLab;
use App\Models\GeneralReportInvestigation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class GeneralReportSourceMobileController extends Controller
{
    private $controllerName = 'GeneralReportSourceMobileController';
    /**
     * Login
     */
    /**
     * List laporan kasus (mobile, paginated)
     *
     * Sama seperti versi web, tapi hasil dipaginasi 10 per halaman (parameter
     * standar Laravel paginator: `?page=`). Otomatis di-scope ke UPT user yang
     * login jika `upt_id > 2`.
     *
     * @param  int  $id  `1`/`0` — filter kolom `training` (data latihan vs data produksi).
     */
    public function getAll(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $current_user = User::find(auth()->user()->id);

            $params = $request->post();
            $query = GeneralReportSource::with('investigation', 'diagnoses.disease', 'lab', 'verification.disease', 'reporter', 'species', 'specieses', 'media', 'user', 'upt', 'location')->where('status', 1)
                ->where('training', $params['id']);

            if ($current_user->upt_id) {
                if ($current_user->upt_id > 2) {
                    $query->where('upt_id', $current_user->upt_id);
                }
            }

            $generalreportsource = $query->orderBy('report_date', 'DESC')->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $generalreportsource
            ]);
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function getOne(Request $request)
    {
        try {
            $params = $request->post();
            $generalreportsource = GeneralReportSource::with(['media', 'user', 'specieses', 'species', 'acknowled', 'lab', 'reporter', 'upt', 'location', 'diagnoses', 'verification', 'verification.media', 'investigation', 'investigation.media'])
                ->where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $generalreportsource
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Buat/ubah laporan kasus (mobile)
     *
     * Payload **nested** per tahap (`reporter.*`, `locations.*`, `species.*`,
     * `diagnoses.*`, dan opsional `verification.*`/`lab.*`/`investigation.*`
     * yang bisa dikirim sekaligus dalam satu request). Mendukung offline-first:
     * kirim `client_id` unik (dibuat di device) sebagai identitas laporan —
     * server akan `create` bila `client_id` belum ada, atau `update` bila sudah
     * ada, sehingga aman untuk retry saat sinkronisasi. Foto base64 dikirim di
     * `file[]`, `verification.file_verification[]`, `investigation.file_investigation[]`.
     */
    public function save(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'client_id' => 'nullable|string',
            'creator' => 'nullable|integer',
            'updater' => 'nullable|integer',
            'training' => 'nullable|boolean',
            'status' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'report_code' => 'nullable|string',
            'report_date' => 'nullable|date',
            'location' => 'nullable|string',
            'species_id' => 'nullable|integer',
            'protected' => 'nullable|boolean',
            'species_name' => 'nullable|string',
            'dead' => 'nullable|integer',
            'live' => 'nullable|integer',
            'description' => 'nullable|string',

            'reporter' => 'nullable|array',
            'reporter.id' => 'nullable|integer',
            'reporter.user_id' => 'nullable|integer',
            'reporter.name' => 'nullable|string',
            'reporter.gender' => 'nullable|string',
            'reporter.occupation' => 'nullable|string',
            'reporter.phone' => 'nullable|string',
            'reporter.address' => 'nullable|string',
            'reporter.case_found' => 'nullable|boolean',

            'locations' => 'nullable|array',
            'locations.id' => 'nullable|integer',
            'locations.upt_id' => 'nullable|integer',
            'locations.upt_type' => 'nullable|string',
            'locations.upt_name' => 'nullable|string',
            'locations.location_name' => 'nullable|string',
            'locations.conservation_type' => 'nullable|string',
            'locations.insitu_conservation' => 'nullable|array',
            'locations.insitu_other' => 'nullable|string',
            'locations.exsitu_conservation' => 'nullable|array',
            'locations.exsitu_other' => 'nullable|string',
            'locations.province_id' => 'nullable|integer',
            'locations.district_id' => 'nullable|integer',
            'locations.subdistrict_id' => 'nullable|integer',
            'locations.village_id' => 'nullable|integer',
            'locations.location_description' => 'nullable|string',
            'locations.latitude' => 'nullable',
            'locations.longitude' => 'nullable',

            'species' => 'nullable|array',
            'species.id' => 'nullable|integer',
            'species.category' => 'nullable|string',
            'species.protected' => 'nullable|boolean',
            'species.protected_species' => 'nullable|integer',
            'species.species_name' => 'nullable|string',
            'species.species_latin_name' => 'nullable|string',
            'species.species_family' => 'nullable|string',
            'species.species_age' => 'nullable|string',
            'species.population' => 'nullable|integer',

            'diagnoses' => 'nullable|array',
            'diagnoses.id' => 'nullable|integer',
            'diagnoses.report_date' => 'nullable|date',
            'diagnoses.dead' => 'nullable|integer',
            'diagnoses.dead_sign' => 'nullable|array',
            'diagnoses.dead_sign_other' => 'nullable|string',
            'diagnoses.live' => 'nullable|integer',
            'diagnoses.live_sign' => 'nullable|array',
            'diagnoses.live_sign_other' => 'nullable|string',
            'diagnoses.chronological' => 'nullable|string',
            'diagnoses.sampling' => 'nullable|string',
            'diagnoses.follow_up' => 'nullable|string',
            'diagnoses.diagnosis' => 'nullable|string',
            'diagnoses.temporary_diagnosis_id' => 'nullable|integer',

            'verification' => 'nullable|array',
            'verification.id' => 'nullable|integer',
            'verification.verified_date' => 'nullable|date',
            'verification.verified' => 'nullable|boolean',
            'verification.verification' => 'nullable|string',
            'verification.temporary_disease_id' => 'nullable|integer',
            'verification.sampling' => 'nullable|string',
            'verification.action' => 'nullable|string',
            'verification.doctor_information' => 'nullable|string',
            'verification.involved_doctors' => 'nullable|array',
            'verification.file_verification' => 'nullable|array',

            'lab' => 'nullable|array',
            'lab.id' => 'nullable|integer',
            'lab.final_disease_id' => 'nullable|integer',
            'lab.final_diagnosis' => 'nullable|string',
            'lab.follow_up' => 'nullable|string',

            'investigation' => 'nullable|array',
            'investigation.id' => 'nullable|integer',
            'investigation.investigation_date_from' => 'nullable|date',
            'investigation.investigation_date_to' => 'nullable|date',
            'investigation.inspection_method' => 'nullable|string',
            'investigation.evidence' => 'nullable|array',
            'investigation.follow_up_carried_out' => 'nullable|string',
            'investigation.additional_information' => 'nullable|string',
            'investigation.data_investigation' => 'nullable|array',
            'investigation.file_investigation' => 'nullable|array',

            'file' => 'nullable|array',
        ]);

        Log::info($request->all());
        $generalreportsource = null;
        DB::transaction(function () use (&$generalreportsource, $request) {
            $user = auth()->user();
            // try {
            $currentUser = User::find($user->id);
            $upt = UPT::find($currentUser->upt_id);
            $params = $request->only([
                'id',
                'creator',
                'updater',
                'training',
                'status',
                'user_id',
                'report_code',
                'report_date',
                'location',
                'species_id',
                'protected',
                'species_name',
                'dead',
                'live',
                'description',
                'client_id',
                'investigation',
                'verification',
                'lab'
            ]);
            $generalreportsource = null;
            if (array_key_exists('client_id', $params) || array_key_exists('id', $params)) {

                // $generalreportsource = GeneralReportSource::where('status', 1)->where('id', $params['id'])->first();
                if (array_key_exists('client_id', $params)) {
                    $generalreportsource = GeneralReportSource::where('client_id', $params['client_id'])->first();
                } else {
                    $generalreportsource = GeneralReportSource::where('id', $params['id'])->first();
                }
                // $generalreportverification = GeneralReportVerification::where('id', $params['id'])->first();
                if ($generalreportsource) {
                    // return [
                    //     'success' => false,
                    //     'message' => "Error, data not found"
                    // ];
                    $this->saveReporter($request, $generalreportsource);
                    $this->saveLocation($request, $generalreportsource);
                    $this->saveSpecies($request, $generalreportsource);
                    $this->saveDiagnoses($request, $generalreportsource);
                    // $this->saveAck($request, $generalreportsource);
                    if (array_key_exists('investigation', $params) && $params['investigation'] != null) {
                        $this->saveInvestigation($request, $generalreportsource);
                    }
                    if (array_key_exists('verification', $params) && $params['verification'] != null) {
                        $this->saveVerify($request, $generalreportsource);
                    }
                    if (array_key_exists('lab', $params) && $params['lab'] != null) {
                        $this->saveLab($request, $generalreportsource);
                    }
                    $this->uploadFile($request, $generalreportsource, 'file');
                } else {

                    $params['report_code'] = $this->generateKode($upt);;
                    $params['training'] = $currentUser->training_mode;
                    $params['creator'] = $user->id;
                    $params['user_id'] = $user->id;
                    $params['upt_id'] = $user->upt_id;
                    $generalreportsource = GeneralReportSource::create($params);
                    // assign new value
                    $params['id'] = $generalreportsource->id;

                    //  parameter samakan
                    $this->GenerateReportReporter($request, $params, $generalreportsource, $upt, $currentUser);
                    $this->GenerateReportLocation($request, $params, $generalreportsource, $upt);
                    $this->GenerateReportSpecies($request, $params, $generalreportsource);
                    $this->GenerateReportDiagnosis($request, $generalreportsource);
                    $this->sendNotificationReport();

                    if (array_key_exists('investigation', $params) && $params['investigation'] != null) {
                        $this->saveInvestigation($request, $generalreportsource);
                    }
                    if (array_key_exists('verification', $params) && $params['verification'] != null) {
                        $this->saveVerify($request, $generalreportsource);
                    }
                    if (array_key_exists('lab', $params) && $params['lab'] != null) {
                        $this->saveLab($request, $generalreportsource);
                    }

                    $this->uploadFile($request, $generalreportsource, 'file');
                }
            }
            // else {
            //     // $params['code'] = $this->generateKode($upt);;
            //     $params['report_code'] = $this->generateKode($upt);;
            //     $params['training'] = $currentUser->training_mode;
            //     $params['creator'] = $user->id;
            //     $params['user_id'] = $user->id;
            //     $params['upt_id'] = $user->upt_id;
            //     $generalreportsource = GeneralReportSource::create($params);
            //     // assign new value
            //     $params['id'] = $generalreportsource->id;

            //     //  parameter samakan
            //     $this->GenerateReportReporter($request, $params, $generalreportsource, $upt, $currentUser);
            //     $this->GenerateReportLocation($request, $params, $generalreportsource, $upt);
            //     $this->GenerateReportSpecies($request, $params, $generalreportsource);
            //     $this->GenerateReportDiagnosis($request, $generalreportsource);
            //     $this->uploadFile($request, $generalreportsource, 'file');
            // }
            // } else {
            // $this->updateUserActivity();
            // $image = $request->input(
            //     'file'
            // );
            // if ($image) {
            //     $images = array_values($image);

            //     foreach($images as $i1){
            //         Log::info($i1);
            //         $generalreportsource->addMediaFromBase64($i1)->usingFileName('foto.png')->toMediaCollection('media');
            //     }
            // }
            // }
        });


        return [
            'success' => true,
            'data' => $generalreportsource
        ];
        // } catch (Exception $e) {
        //     Log::error($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
        //     return [
        //         'success' => false,
        //         'message' => "Error, cannot save data"
        //     ];
        // }
    }

    public function saveReporter($request, $reporter)
    {
        $params = $request->only([
            'reporter.id',
            'reporter.user_id',
            'reporter.name',
            'reporter.gender',
            'reporter.occupation',
            'reporter.phone',
            'reporter.address',
            'reporter.case_found',
            'client_id'
        ]);
        if (count($params) > 0) {
            // if ($params['id']) {
            //     $generalReporter = GeneralReportReporter::where('id', $params['id'])->first();
            // } elseif ($params['client_id']) {
            // }
            if ($params['client_id']) {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('id', $params['reporter']['id'])->first();
            }
            $generalReporter = GeneralReportReporter::where('id', $generalReportSource->id)->first();

            if ($generalReporter) {
                Log::error($params);
                Log::info($params);
                $data1 = $generalReporter->toArray();
                $data2 = $params;

                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));

                $generalReport = array_map('unserialize', $diff);
                if (
                    count($generalReport) == 0
                    // $generalReporter->id == $params['id'] &&
                    // $generalReporter->user_id == $params['user_id'] &&
                    // $generalReporter->name == $params['name'] &&
                    // $generalReporter->gender == $params['gender'] &&
                    // $generalReporter->occupation == $params['occupation'] &&
                    // $generalReporter->phone == $params['phone'] &&
                    // $generalReporter->address == $params['address'] &&
                    // $generalReporter->case_found == $params['case_found']
                ) {
                    return false;
                }

                // if (array_key_exists('client_id', $params)) {
                // $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
                GeneralReportReporter::where('id', $generalReportSource->id)->update([
                    'user_id' => $params['reporter']['user_id'] ?? null,
                    'name' => $params['reporter']['name'] ?? null,
                    'gender' => $params['reporter']['gender'] ?? null,
                    'occupation' => $params['reporter']['occupation'] ?? null,
                    'phone' => $params['reporter']['phone'] ?? null,
                    'address' => $params['reporter']['address'] ?? null,
                    'case_found' => $params['reporter']['case_found'] ?? null,
                ]);
                // } else {
                //     GeneralReportReporter::where('id', $params['id'])->update([
                //         'user_id' => $params['user_id'],
                //         'name' => $params['name'],
                //         'gender' => $params['gender'],
                //         'occupation' => $params['occupation'],
                //         'phone' => $params['phone'],
                //         'address' => $params['address'],
                //         'case_found' => $params['case_found'],
                //     ]);
                // }
                return true;
            }
            Log::info($params);
            $generalReporter = new GeneralReportReporter();
            $generalReportArray = $generalReporter->toArray($params);
            $generalReporter->fill($generalReportArray);
            $generalReporter->save();
            return true;
        }
    }

    public function saveLocation(Request $request, $reporter)
    {
        $params = $request->only([
            'locations.id',
            'locations.upt_id',
            'locations.upt_type',
            'locations.upt_name',
            'locations.location_name',
            'locations.conservation_type',
            'locations.insitu_conservation',
            'locations.insitu_other',
            'locations.exsitu_conservation',
            'locations.exsitu_other',
            'locations.province_id',
            'locations.district_id',
            'locations.subdistrict_id',
            'locations.village_id',
            'locations.location_description',
            'locations.latitude',
            'locations.longitude',
            'client_id'
        ]);
        if (count($params) > 0) {
            if ($params['client_id']) {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            }
            $generalLocations = GeneralReportLocation::where('id', $generalReportSource->id)->first();
            if ($generalLocations) {
                $data1 = $generalLocations->toArray();
                $data2 = $params;
                // Log::error($data1);
                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));

                $generalLoc = array_map('unserialize', $diff);
                if (
                    count($generalLoc) == 0
                    // $generalLocations->id == $params['id'] &&
                    // $generalLocations->upt_id == $params['upt_id'] &&
                    // $generalLocations->upt_type == $params['upt_type'] &&
                    // $generalLocations->upt_name == $params['upt_name'] &&
                    // $generalLocations->location_name == $params['location_name'] &&
                    // $generalLocations->conservation_type == $params['conservation_type'] &&
                    // $generalLocations->insitu_conservation == $params['insitu_conservation'] &&
                    // $generalLocations->insitu_other == $params['insitu_other'] &&
                    // $generalLocations->exsitu_conservation == $params['exsitu_conservation'] ?? null &&
                    // $generalLocations->exsitu_other == $params['exsitu_other'] &&
                    // $generalLocations->province_id == $params['province_id'] &&
                    // $generalLocations->district_id == $params['district_id'] &&
                    // $generalLocations->subdistrict_id == $params['subdistrict_id'] &&
                    // $generalLocations->village_id == $params['village_id'] &&
                    // $generalLocations->location_description == $params['location_description'] &&
                    // $generalLocations->latitude == $params['latitude'] &&
                    // $generalLocations->longitude == $params['longitude']
                ) {
                    return false;
                }
                Log::error($params);
                GeneralReportLocation::where('id', $generalReportSource->id)->update([
                    'upt_id' => $params['locations']['upt_id'] ?? null,
                    'upt_type' => $params['locations']['upt_type'] ?? null,
                    'upt_name' => $params['locations']['upt_name'] ?? null,
                    'location_name' => $params['locations']['location_name'] ?? null,
                    'conservation_type' => $params['locations']['conservation_type'] ?? null,
                    'insitu_conservation' => $params['locations']['insitu_conservation'] ?? null,
                    'insitu_other' => $params['locations']['insitu_other'] ?? null,
                    'exsitu_conservation' => $params['locations']['exsitu_conservation'] ?? null,
                    'exsitu_other' => $params['locations']['exsitu_other'] ?? null,
                    'province_id' => $params['locations']['province_id'] ?? null,
                    'district_id' => $params['locations']['district_id'] ?? null,
                    'subdistrict_id' => $params['locations']['subdistrict_id'] ?? null,
                    'village_id' => $params['locations']['village_id'] ?? null,
                    'location_description' => $params['locations']['location_description'] ?? null,
                    'latitude' => $params['locations']['latitude'] ?? null,
                    'longitude' => $params['locations']['longitude'] ?? null,
                ]);
                return true;
            }
            $generalLocations = new GeneralReportLocation();
            // $generaLocationArray = $generalLocations->toArray($params);
            // $generalLocations->fill($generaLocationArray);

            $generalLocations->save();
            return true;
        }
    }

    public function saveSpecies(Request $request, $reporter)
    {
        $params = $request->only([
            'species.id',
            'species.category',
            'species.protected',
            'species.protected_species',
            'species.species_name',
            'species.species_latin_name',
            'species.species_family',
            'species.species_age',
            'species.population',
            'client_id'
        ]);
        if (count($params) > 0) {
            Log::error($params);
            if ($params['client_id']) {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            }
            $generalSpeciess = GeneralReportSpecies::where('id', $generalReportSource->id)->first();
            if ($generalSpeciess) {
                // Log::error($params);
                $data1 = $generalSpeciess->toArray();
                $data2 = $params;
                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));

                $generalSpec = array_map('unserialize', $diff);
                if (
                    count($generalSpec) == 0
                    // $generalSpeciess->id == $params['id'] &&
                    // $generalSpeciess->category == $params['category'] &&
                    // $generalSpeciess->protected == $params['protected'] &&
                    // $generalSpeciess->protected_species == $params['protected_species'] &&
                    // $generalSpeciess->species_name == $params['species_name'] &&
                    // $generalSpeciess->species_latin_name == $params['species_latin_name'] &&
                    // $generalSpeciess->species_family == $params['species_family'] &&
                    // $generalSpeciess->species_age == $params['species_age'] &&
                    // $generalSpeciess->population == $params['population']
                ) {
                    return false;
                }

                GeneralReportSpecies::where('id', $generalReportSource->id)->update([
                    'category' => $params['species']['category'] ?? null,
                    'protected' => $params['species']['protected'] ?? null,
                    'protected_species' => $params['species']['protected_species'] ?? null,
                    'species_name' => $params['species']['species_name'] ?? null,
                    'species_latin_name' => $params['species']['species_latin_name'] ?? null,
                    'species_family' => $params['species']['species_family'] ?? null,
                    'species_age' => $params['species']['species_age'] ?? null,
                    'population' => $params['species']['population'] ?? null,
                ]);
                return true;
            }

            $generalSpeciess = new GeneralReportSpecies();
            // $generalSpeciesArray = $generalSpeciess->toArray($params);
            // $generalSpeciess->fill($generalSpeciesArray);
            $generalSpeciess->category = $params['species']['category'] ?? null;
            $generalSpeciess->protected = $params['species']['protected'] ?? null;
            $generalSpeciess->protected_species = $params['species']['protected_species'] ?? null;
            $generalSpeciess->species_name = $params['species']['species_name'] ?? null;
            $generalSpeciess->species_latin_name = $params['species']['species_latin_name'] ?? null;
            $generalSpeciess->species_age = $params['species']['species_age'] ?? null;
            $generalSpeciess->population = $params['species']['population'] ?? null;
            $generalSpeciess->save();
            return true;
        }
    }

    public function saveDiagnoses(Request $request, $reporter)
    {
        $params = $request->only([
            "diagnoses.id",
            'diagnoses.report_date',
            'diagnoses.dead',
            'diagnoses.dead_sign',
            'diagnoses.dead_sign_other',
            'diagnoses.live',
            'diagnoses.live_sign',
            'diagnoses.live_sign_other',
            'diagnoses.chronological',
            'diagnoses.sampling',
            'diagnoses.follow_up',
            'diagnoses.diagnosis',
            'diagnoses.temporary_diagnosis_id',
            'diagnoses.dead_sign',
            'diagnoses.live_sign',
            'client_id'
        ]);
        if (count($params) > 0) {
            if ($params['client_id']) {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            }
            $generalDiagnoses = GeneralReportDiagnosis::where('id', $generalReportSource->id)->first();
            if ($generalDiagnoses) {
                $data1 = $generalDiagnoses->toArray();
                $data2 = $params;

                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));
                $generalDiag = array_map('unserialize', $diff);
                if (
                    count($generalDiag) == 0
                    // $generalDiagnoses->id == $generalReportSource->id &&
                    // $generalDiagnoses->report_date == $params['report_date'] &&
                    // $generalDiagnoses->dead == $params['dead'] &&
                    // $generalDiagnoses->dead_sign == $params['dead_sign'] &&
                    // $generalDiagnoses->live == $params['live'] &&
                    // $generalDiagnoses->live_sign == $params['live_sign'] &&
                    // $generalDiagnoses->chronological == $params['chronological'] &&
                    // $generalDiagnoses->sampling == $params['sampling'] &&
                    // $generalDiagnoses->follow_up == $params['follow_up'] &&
                    // $generalDiagnoses->diagnosis == $params['diagnosis'] &&
                    // $generalDiagnoses->temporary_diagnosis_id == $params['temporary_diagnosis_id']
                ) {
                    return false;
                }

                $generalDiagnosis = GeneralReportDiagnosis::where('id', $generalReportSource->id)->update([
                    'report_date' => $params['diagnoses']['report_date'] ?? null,
                    'dead' => $params['diagnoses']['dead'] ?? null,
                    'dead_sign' => $params['diagnoses']['dead_sign'] ?? null,
                    'dead_sign_other' => $params['diagnoses']['dead_sign_other'] ?? null,
                    'live' => $params['diagnoses']['live'] ?? null,
                    'live_sign' => $params['diagnoses']['live_sign'] ?? null,
                    'live_sign_other' => $params['diagnoses']['live_sign_other'] ?? null,
                    'chronological' => $params['diagnoses']['chronological'] ?? null,
                    'sampling' => $params['diagnoses']['sampling'] ?? null,
                    'follow_up' => $params['diagnoses']['follow_up'] ?? null,
                    'diagnosis' => $params['diagnoses']['diagnosis'] ?? null,
                    'temporary_diagnosis_id' => $params['diagnoses']['temporary_diagnosis_id'] ?? null,
                ]);
                return [
                    'success' => true,
                    'data' => $generalDiagnosis
                ];
            }

            $generalDiagnoses = new GeneralReportDiagnosis();
            $generalReportDiagnosesArray = $generalDiagnoses->toArray($params);
            $generalDiagnoses->fill($generalReportDiagnosesArray);
            $generalDiagnoses->save();
            return [
                'success' => true,
                'data' => $generalDiagnoses
            ];
        }
    }

    public function saveAck(Request $request, $reporter)
    {
        $params = $request->only([
            'id',
            'upt_head_date',
            'upt_head_name',
            'upt_head_occupation',
            'upt_head_sign',
        ]);
        if (count($params) > 0) {
            $generalAck = GeneralReportAcknowledgement::where('id', $params['id'])->first();
            if ($generalAck) {
                if (
                    $generalAck->id == $params['id'] &&
                    $generalAck->upt_head_date == $params['upt_head_date'] &&
                    $generalAck->upt_head_name == $params['upt_head_name'] &&
                    $generalAck->upt_head_occupation == $params['upt_head_occupation'] &&
                    $generalAck->upt_head_sign == $params['upt_head_sign']
                ) {
                    return false;
                }

                GeneralReportAcknowledgement::where('id', $params['id'])->update([
                    'upt_head_date' => $params['upt_head_date'] ?? null,
                    'upt_head_name' => $params['upt_head_name'] ?? null,
                    'upt_head_occupation' => $params['upt_head_occupation'] ?? null,
                    'upt_head_sign' => $params['upt_head_sign'] ?? null,
                ]);
                return true;
            }

            $generalAck = new GeneralReportAcknowledgement();
            $generalAckArray = $generalAck->toArray($params);
            $generalAck->fill($generalAckArray);
            $generalAck->id = $params['id']; //tambahan
            $generalAck->save();
        }
    }

    public function saveVerify($request, $generalreportsource)
    // public function saveVerify(Request $request)
    {
        $params = $request->only([
            "verification.id",
            // 'doctor_name',
            // 'doctor_occupation',
            'verification.verified_date',
            'verification.verified',
            'verification.verification',
            'verification.temporary_disease_id',
            'verification.sampling',
            'verification.action',
            'verification.doctor_information',
            'verification.involved_doctors',
            'client_id'
        ]);
        // if (count($request['verification']) > 0) {
        if (count($params) > 0) {
            // if (array_key_exists('id', $params)) {
            //     $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            // } else {
            //     $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            // }
            // if ($generalReportSource) {
            $generalVerify = GeneralReportVerification::where('id', $generalreportsource->id)->first();
            if ($generalVerify) {
                Log::error($params);
                $data1 = $generalVerify->toArray();
                $data2 = $params;

                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));
                $generalVer = array_map('unserialize', $diff);
                if (
                    count($generalVer) == 0
                    // $generalVerify->id == $params['id'] &&
                    // $generalVerify->doctor_name == $params['doctor_name'] &&
                    // $generalVerify->doctor_occupation == $params['doctor_occupation'] &&
                    // $generalVerify->verified_date == $params['verified_date'] &&
                    // $generalVerify->verified == $params['verified'] &&
                    // $generalVerify->verification == $params['verification'] &&
                    // $generalVerify->temporary_disease_id == $params['temporary_disease_id'] &&
                    // $generalVerify->sampling == $params['sampling'] &&
                    // $generalVerify->action == $params['action'] &&
                    // $generalVerify->doctor_information == $params['doctor_infomration']
                ) {
                    return false;
                }

                $generalreportverification = GeneralReportVerification::where('id', $generalreportsource->id)->update([
                    // 'doctor_name' => $params['doctor_name'] ?? null,
                    // 'doctor_occupation' => $params['doctor_occupation'] ?? null,
                    'verified_date' => $params['verification']['verified_date'] ?? null,
                    'verified' => $params['verification']['verified'] ?? null,
                    'verification' => $params['verification']['verification'] ?? null,
                    'temporary_disease_id' => $params['verification']['temporary_disease_id'] ?? null,
                    'sampling' => $params['verification']['sampling'] ?? null,
                    'action' => $params['verification']['action'] ?? null,
                    'doctor_information' => $params['verification']['doctor_information'] ?? null,
                    'involved_doctors' => $params['verification']['involved_doctors'] ?? null,
                ]);

                // $this->uploadFile($request, $generalVerify, 'file_verification');
                $this->uploadFile($request, $generalVerify, 'verification.file_verification');
                return [
                    'success' => true,
                    'data' => $generalreportverification
                ];
            }
            // }

            $generalreportverification = new GeneralReportVerification();
            // $generalVerifyArray = $generalVerify->toArray($params);
            // $generalreportverification->fill($params);
            $generalreportverification->id = $generalreportsource->id; // tambahan
            $generalreportverification->verified_date = $params['verification']['verified_date'] ?? null;
            $generalreportverification->verified = $params['verification']['verified'] ?? 0;
            $generalreportverification->verification = $params['verification']['verification'] ?? null;
            $generalreportverification->temporary_disease_id = $params['verification']['temporary_disease_id'] ?? null;
            $generalreportverification->sampling = $params['verification']['sampling'] ?? null;
            $generalreportverification->action = $params['verification']['action'] ?? null;
            $generalreportverification->doctor_information = $params['verification']['doctor_information'] ?? null;
            $generalreportverification->involved_doctors = $params['verification']['involved_doctors'] ?? null;
            $generalreportverification->save();

            $this->uploadFile($request, $generalreportverification, 'verification.file_verification');

            return [
                'success' => true,
                'data' => $generalreportverification
            ];
        } else {
            return true;
        }
    }

    public function saveLab($request, $generalreportsource)
    {
        $params = $request->only([
            'lab.id',
            'lab.final_disease_id',
            'lab.final_diagnosis',
            'lab.follow_up',
            // 'client_id'
        ]);
        // if (count($request['lab']) > 0) {
        if (count($params) > 0) {
            // if (array_key_exists('id', $params)) {
            //     $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            // } else {
            //     $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            // }
            // if ($generalReportSource) {
            $generalLab = GeneralReportLab::where('id', $generalreportsource->id)->first();
            if ($generalLab) {
                $data1 = $generalLab->toArray();
                $data2 = $params;

                $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));
                $generalLab = array_map('unserialize', $diff);
                if (
                    count($generalLab) == 0
                    // $generalLab->id  == $params['id'] &&
                    // $generalLab->final_disease_id == $params['final_disease_id'] &&
                    // $generalLab->final_diagnosis == $params['final_diagnosis'] &&
                    // $generalLab->follow_up == $params['follow_up']
                ) {
                    return false;
                }

                $lab = GeneralReportLab::where('id', $generalreportsource->id)->update([
                    'final_disease_id' => $params['lab']['final_disease_id'],
                    'final_diagnosis' => $params['lab']['final_diagnosis'],
                    'follow_up' => $params['lab']['follow_up']
                ]);
                // return true;
                return [
                    'success' => true,
                    'data' => $lab
                ];
            }
            // }

            $lab = new GeneralReportLab();
            // $generalLabArray = $generalLab->toArray($params);
            $lab->fill($params);
            // $lab->id = $params['id'];
            $lab->id = $generalreportsource->id;
            $lab->final_disease_id = $params['lab']['final_disease_id'] ?? null;
            $lab->final_diagnosis = $params['lab']['final_diagnosis'] ?? null;
            $lab->follow_up = $params['lab']['follow_up'] ?? null;
            $lab->save();

            return [
                'success' => true,
                'data' => $lab
            ];
        } else {
            return true;
        }
    }

    public function saveInvestigation($request, $generalreportsource)
    {
        // return $request->post();
        Log::info($request);
        try {
            $params = $request->only([
                'investigation.id',
                'investigation.investigation_date_from',
                'investigation.investigation_date_to',
                'investigation.inspection_method',
                'investigation.evidence',
                'investigation.follow_up_carried_out',
                'investigation.additional_information',
                'investigation.data_investigation',
                'client_id'
            ]);

            if (count($params) > 0) {
                // return $params['id'];
                // if (array_key_exists('id', $params)) {
                //     $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
                // } else {
                //     $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
                // }
                // if ($generalReportSource) {
                $investigations = GeneralReportInvestigation::where('id', $generalreportsource->id)->first();
                if ($investigations) {
                    $data1 = $investigations->toArray();
                    $data2 = $params;

                    $diff = array_diff(array_map('serialize', $data1), array_map('serialize', $data2));
                    $investigationsArray = array_map('unserialize', $diff);
                    if (
                        count($investigationsArray) == 0
                        // $generalLab->id  == $params['id'] &&
                        // $generalLab->final_disease_id == $params['final_disease_id'] &&
                        // $generalLab->final_diagnosis == $params['final_diagnosis'] &&
                        // $generalLab->follow_up == $params['follow_up']
                    ) {
                        return false;
                    }

                    $investigation = GeneralReportInvestigation::where('id', $generalreportsource->id)->update([
                        'investigation_date_from' => $params['investigation']['investigation_date_from'],
                        'investigation_date_to' => $params['investigation']['investigation_date_to'],
                        'inspection_method' => $params['investigation']['inspection_method'],
                        'evidence' => $params['investigation']['evidence'],
                        'follow_up_carried_out' => $params['investigation']['follow_up_carried_out'],
                        'additional_information' => $params['investigation']['additional_information'],
                        'data_investigation' => $params['investigation']['data_investigation']
                    ]);

                    $this->uploadFile($request, $investigations, 'investigation.file_investigation');
                    Log::info($request->file_investigation);
                    return [
                        'success' => true,
                        'data' => $investigation
                    ];
                }
                $investigation1 = new GeneralReportInvestigation();
                // $generalInArray = $investigation->toArray($params);
                // $investigation->fill($generalInArray);
                // $investigation->fill($params);
                // $investigation->id = $params['id'];
                $investigation1->id = $generalreportsource->id;
                $investigation1->investigation_date_from = $params['investigation']['investigation_date_from'] ?? null;
                $investigation1->investigation_date_to = $params['investigation']['investigation_date_to'] ?? null;
                $investigation1->inspection_method = $params['investigation']['inspection_method'] ?? null;
                $investigation1->evidence = $params['investigation']['evidence'] ?? null;
                $investigation1->follow_up_carried_out = $params['investigation']['follow_up_carried_out'] ?? null;
                $investigation1->additional_information = $params['investigation']['additional_information'] ?? null;
                $investigation1->data_investigation = $params['investigation']['data_investigation'] ?? null;
                $investigation1->save();
                $investigation2 = GeneralReportInvestigation::where('id', $generalreportsource->id)->first();

                $this->uploadFile($request, $investigation2, 'investigation.file_investigation');
                Log::info($request->file_investigation);

                return [
                    'success' => true,
                    'data' => $investigation1
                ];
                // }

            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    private  function updateUserActivity()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;
        $user_activity = UserActivity::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $data = [
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
            'last_seen' => $now,
            'send_report' => true,
        ];

        if ($user_activity) {
            $user_activity->fill($data);
            $user_activity->save();
        } else {
            UserActivity::create($data);
        }
    }

    private function generateKode($upt)
    {
        $lastRecord = GeneralReportSource::latest()->first();
        // Generate kode:
        // 1. nomor id Ambil dari general report source record terakhir 
        //. pakai variabel biasa
        // 2. id + 1
        $id = $lastRecord ? $lastRecord->id : 0;
        $id++;
        // 3. dari upt -> ambil nama, hilangkan semua spasi " " pakai str replace, ambil 12 digit pertama
        $nama_upt = str_replace(" ", "", $upt->name);
        $nama_upt = substr($nama_upt, 0, 12);
        // 4. dari upt -> ambil type
        $type = $upt->type;
        // Kode = "LU-" . tipe upt . nama upt yg uda di proses . id yang uda di + 1
        $kode = "LU" . $type . $nama_upt . $id;
        return  $kode;
    }

    private function GenerateReportLocation($request, $source, $params, $upt)
    {
        $post = $request->only([
            'id',
            'upt_id',
            'upt_type',
            // 'upt_name',
            'location_name',
            'conservation_type',
            'insitu_conservation',
            'insitu_other',
            'exsitu_conservation',
            'exsitu_other',
            'province_id',
            'district_id',
            'subdistrict_id',
            'village_id',
            'location_description',
            'latitude',
            'longitude',
        ]);
        // $generalLocations = new GeneralReportLocation();
        // $generaLocationArray = $generalLocations->toArray($post);
        // $generalLocations->fill($generaLocationArray);
        // $generalLocations->save();

        // $generalLocation_array = $upt->toArray();

        $generalLocation  = new GeneralReportLocation();
        // $generalLocation->fill($generalLocation_array);
        $generalLocation->id = $params['id']; //source->id
        $generalLocation->upt_id = $post['upt_id'] ?? null;
        $generalLocation->upt_type = $post['upt_type'] ?? null;
        $generalLocation->location_name =  $post['location_name'] ?? null;
        $generalLocation->conservation_type =  $post['conservation_type']  ?? null;
        $generalLocation->insitu_conservation =  $post['insitu_conservation'] ?? null;
        $generalLocation->insitu_other =  $post['insitu_other'] ?? null;
        $generalLocation->exsitu_conservation =  $post['exsitu_conservation'] ?? null;
        $generalLocation->exsitu_other =  $post['exsitu_other'] ?? null;
        $generalLocation->province_id =  $post['province_id'] ?? null;
        $generalLocation->district_id =  $post['district_id'] ?? null;
        $generalLocation->subdistrict_id =  $post['subdistrict_id'] ?? null;
        $generalLocation->village_id =  $post['village_id'] ?? null;
        $generalLocation->location_description =  $post['location_description'] ?? null;
        $generalLocation->latitude =  $post['latitude'] ?? null;
        $generalLocation->longitude =  $post['longitude'] ?? null;
        $generalLocation->save();
    }

    private function GenerateReportSpecies($request, $params, $generalreportsource)
    {
        $post = $request->only([
            'id',
            'category',
            'protected',
            'protected_species',
            'species_name',
            'species_latin_name',
            'species_family',
            'species_age',
            'population',
        ]);

        Log::info(array_key_exists("protected", $params) && $params['protected']);
        if (array_key_exists("protected", $params) && $params['protected']) {
            $species = Species::where('id', $generalreportsource->species_id)->first();

            $generalSpecies = new GeneralReportSpecies();
            $generalSpecies->id = $generalreportsource->id;
            $generalSpecies->protected_species = $generalreportsource->species_id;
            $generalSpecies->category = $species->category;
            $generalSpecies->species_name = $species->name;
            $generalSpecies->species_latin_name = $species->latin_name;
            $generalSpecies->species_family = $species->family;
            $generalSpecies->species_age = $post['species_age'] ?? null;
            $generalSpecies->population = $post['population'] ?? null;
            $generalSpecies->save();
        } else {
            $generalSpecies = new GeneralReportSpecies();
            $generalSpecies->id = $generalreportsource->id;
            $generalSpecies->category = $post['category'] ?? null;
            $generalSpecies->protected = $post['protected'] ?? null;
            $generalSpecies->protected_species = $post['protected_species'] ?? null;
            $generalSpecies->species_name = $post['species_name'] ?? null;
            $generalSpecies->species_latin_name = $post['species_latin_name'] ?? null;
            $generalSpecies->species_family = $post['species_family'] ?? null;
            $generalSpecies->species_age = $post['species_age'] ?? null;
            $generalSpecies->population = $post['population'] ?? null;
            $generalSpecies->save();
        }
    }

    public function GenerateReportDiagnosis($request, $generalreportsource)
    {
        $params = $request->only([
            "id",
            'report_date',
            'dead',
            'dead_sign',
            'dead_sign_other',
            'live',
            'live_sign',
            'live_sign_other',
            'chronological',
            'sampling',
            'follow_up',
            'diagnosis',
            'temporary_diagnosis_id',
            'dead_sign',
            'live_sign'
        ]);

        $generalDiagnosis = new GeneralReportDiagnosis();
        $generalDiagnosis->id = $generalreportsource->id;
        $generalDiagnosis->report_date = $params['report_date'] ?? null;
        $generalDiagnosis->dead = $params['dead'] ?? null;
        $generalDiagnosis->dead_sign = $params['dead_sign'] ?? null;
        $generalDiagnosis->dead_sign_other = $params['dead_sign_other'] ?? null;
        $generalDiagnosis->live = $params['live'] ?? null;
        $generalDiagnosis->live_sign = $params['live_sign'] ?? null;
        $generalDiagnosis->live_sign_other = $params['live_sign_other'] ?? null;
        $generalDiagnosis->chronological = $params['chronological'] ?? null;
        $generalDiagnosis->sampling = $params['sampling'] ?? null;
        $generalDiagnosis->follow_up = $params['follow_up'] ?? null;
        $generalDiagnosis->diagnosis = $params['diagnosis'] ?? null;
        $generalDiagnosis->temporary_diagnosis_id = $params['temporary_diagnosis_id'] ?? null;
        $generalDiagnosis->dead_sign = $params['dead_sign'] ?? null;
        $generalDiagnosis->live_sign = $params['live_sign'] ?? null;
        $generalDiagnosis->save();
    }

    public function GenerateReportReporter($request, $params, $currentUser, $source, $upt)
    {
        $post = $request->only([
            'id',
            'user_id',
            'name',
            'gender',
            'occupation',
            'phone',
            'address',
            'case_found',
        ]);
        Log::info($post);
        // $generalReporter = new GeneralReportReporter();
        // $generalReportArray = $generalReporter->toArray($post);
        // $generalReporter->fill($generalReportArray);
        // $generalReporter->save();
        // return true;

        $reporter = new GeneralReportReporter();
        $currentUser_array = $currentUser->toArray();
        $reporter->fill($currentUser_array);
        $reporter->id = $params['id']; //source->id;
        $reporter->name = $post['name'] ?? null;
        $reporter->gender = $post['gender'] ?? null;
        $reporter->occupation = $post['occupation'] ?? null;
        $reporter->phone = $post['phone'] ?? null;
        $reporter->address = $upt->address ?? $post['address'] ?? null;
        $reporter->case_found = true;
        $reporter->save();
    }
    public function uploadFile($request, $data, $only)
    {
        try {

            // $id = $request->input('id');


            // $generalreportsource = GeneralReportSource::with('media')
            //     ->where('status', 1)
            //     ->where('id', $id)
            //     ->first();


            // foreach ($generalreportsource->media as $id => $media) {
            //     $media->delete();
            // }


            // $generalreportsource->addMediaFromRequest('file')
            //     ->toMediaCollection();

            // $generalreportsource = GeneralReportSource::with('media')
            //     ->where('status', 1)
            //     ->where('id', $id)
            //     ->first();

            $image = $request->input(
                $only
            );
            if ($image) {
                $images = array_values($image);

                foreach ($images as $i1) {
                    Log::info($i1);
                    if (is_string($i1)) {
                        $data->addMediaFromBase64($i1)->usingFileName('foto.png')->toMediaCollection('media');
                    } else {
                        if (array_key_exists('deleted', $i1)) {
                            $media = Media::find($i1['id'])->delete();
                        }
                    }
                }
            }

            return [
                'success' => true,
                // 'data' => $generalreportsource
            ];
        } catch (Exception $e) {
            Log::error($this->controllerName . '-uploadFile: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function deleteFile(Request $request)
    {
        try {
            $params = $request->post();

            GeneralReportSource::where('id', $params['id'])->delete();
            DB::table('media')->where('model_id', $params['id'])->delete();
            return [
                'success' => true,
                'message' => 'Berhasil di hapus'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error delete data'
            ];
        }
    }
    /**
     * Hapus laporan kasus (mobile, soft delete)
     *
     * Berbeda dari versi web: identifikasi laporan lewat `client_id` (bukan `id`
     * server), sesuai skema offline-first mobile app. Hanya set `status = 0`.
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'client_id' => 'required|string',
            ]);

            $params = $request->post();
            $generalreportsource = null;

            if (!array_key_exists('client_id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $generalreportsource = GeneralReportSource::where('status', 1)->where('client_id', $params['client_id'])->first();
            if (!$generalreportsource) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $generalreportsource->status = 0;
            $generalreportsource->save();

            return [
                'success' => true,
                'data' => $generalreportsource
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-delete: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot delete data"
            ];
        }
    }

    public function tampilinSemua()
    {
        $query = GeneralReportSource::with('investigation', 'diagnoses.disease', 'lab', 'verification.disease', 'reporter', 'species', 'specieses', 'media', 'user', 'upt', 'location')->where('status', 1)->get();
        return $query;
    }

    /**
     * Kirim notifikasi laporan baru
     *
     * Dipanggil otomatis oleh `save()` setiap kali laporan baru dibuat. Mengirim
     * push notification (FCM) "Laporan Kasus Sehatsatli" ke user dengan
     * `all_notification = true` di UPT-type yang sama, kepala UPT, dan UPT
     * `id = 1`. Tidak menerima parameter.
     */
    public function sendNotificationReport()
    {
                // jika terima semua notifikasi di aktifkan
        // semua petugas TN KSDA LK LU yg sama
        $SERVER_API_KEY = env('APP_SERVER_API_KEY');

        $users = User::where('all_notification', 1)
            ->where('upt_type', auth()->user()->upt_type)
            ->whereNotNull('devices')
            ->get();

        // point 1
        $tokenNotification = [];
        foreach ($users as $key => $value) {
            $masuk = $value->heads_upt === true;
            $upt = $value->upt_id === 1;
            if ($masuk || $upt) {
                array_push($tokenNotification, $value->devices);
            }
        }

        $firebasePETUGAS = User::where('user_level', 2)
            ->orWhere('user_level', 4)
            ->orWhere('all_notification', 1)
            ->orWhere('heads_upt', 1)
            ->where('status', 1)
            ->get();

        $subject = 'SehatSatli';
        $body = 'Laporan Kasus Sehatsatli';
        $dataJson = "Laporan Kasus Sehatsatli";

        // foreach ($firebasePETUGAS as $key => $item) {
        //     $userInbox = new UserInbox();
        //     // $userInbox->creator = auth()->user()->id;
        //     // $userInbox->updater = auth()->user()->id;
        //     $userInbox->status = 0;
        //     $userInbox->user_id = $item->id;
        //     $userInbox->received_date = date('Y-m-d');
        //     $userInbox->read_date = date('Y-m-d');
        //     $userInbox->subject =  $subject;
        //     $userInbox->message = $body;
        //     // $userInbox->read = ;
        //     $userInbox->save();
        // }

        $data = array(
            'registration_ids' => $tokenNotification,
            'notification' => [
                'title' => $subject,
                'body' => $body,
                // "click_action" => "https://example.com/chat"
            ],
            "data" =>   ["data" =>  json_encode($dataJson)],
        );

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return true;
    }
}
