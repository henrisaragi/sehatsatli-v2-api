<?php

namespace App\Http\Controllers;

use App\Models\Disease;
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
use App\Models\UserInbox;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\CommunityReport;


class GeneralReportSourceController extends Controller
{
    //
    private $controllerName = 'GeneralReportSourceController';
    /**
     * Login
     */
    /**
     * List laporan kasus (web)
     *
     * Mengembalikan seluruh laporan (`GeneralReportSource`) beserta relasinya
     * (investigasi, diagnosis, lab, verifikasi dokter, pelapor, spesies, foto,
     * user, UPT, lokasi), diurutkan dari `report_date` terbaru. Otomatis
     * di-scope ke UPT user yang login jika `upt_id > 2`.
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

            $generalreportsource = $query->orderBy('report_date', 'DESC')->get();

            return response()->json([
                'success' => true,
                'data' => $generalreportsource,
            ]);
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Detail 1 laporan kasus
     *
     * Mengembalikan satu `GeneralReportSource` beserta seluruh relasinya (pelapor,
     * lokasi, spesies, diagnosis awal, verifikasi dokter + foto, investigasi + foto,
     * hasil lab, pengesahan kepala UPT, foto laporan).
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

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
     * Cek id terakhir GeneralReportSource
     *
     * Mengembalikan `id` record `GeneralReportSource` terakhir (berdasarkan
     * `created_at` terbaru, mengikuti logika `generateKode()`). Berguna untuk
     * preview nomor urut/`report_code` berikutnya di sisi client sebelum submit.
     */
    public function getLastId()
    {
        try {
            $lastRecord = GeneralReportSource::latest()->first();

            return [
                'success' => true,
                'data' => [
                    'last_id' => $lastRecord ? $lastRecord->id : 0,
                ],
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getLastId: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Buat/ubah laporan kasus (web)
     *
     * Payload flat berisi field dari beberapa tahap sekaligus (data laporan induk,
     * pelapor, lokasi, spesies, diagnosis awal). Kirim `id` untuk mengubah laporan
     * yang sudah ada, kosongkan untuk membuat laporan baru (server akan generate
     * `report_code` otomatis). Setiap sub-bagian (reporter/location/species/
     * diagnoses) hanya diproses ulang jika ada perbedaan dengan data tersimpan.
     * Foto dikirim base64 di `file[]`.
     */
    public function save(Request $request)
    {
        $request->validate([
            // Laporan induk
            'id' => 'nullable|integer',
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
            'protected_species' => 'nullable|integer',
            'species_name' => 'nullable|string',
            'dead' => 'nullable|integer',
            'live' => 'nullable|integer',
            'description' => 'nullable|string',
            'client_id' => 'nullable|string',
            // Pelapor (GeneralReportReporter)
            'name' => 'nullable|string',
            'gender' => 'nullable|string',
            'occupation' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'case_found' => 'nullable|boolean',
            'reporter_id' => 'nullable|integer',
            // Lokasi (GeneralReportLocation)
            'upt_id' => 'nullable|integer',
            'upt_type' => 'nullable|string',
            'upt_name' => 'nullable|string',
            'location_name' => 'nullable|string',
            'conservation_type' => 'nullable|string',
            'insitu_conservation' => 'nullable|array',
            'insitu_other' => 'nullable|string',
            'exsitu_conservation' => 'nullable|array',
            'exsitu_other' => 'nullable|string',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'subdistrict_id' => 'nullable|integer',
            'village_id' => 'nullable|integer',
            'location_description' => 'nullable|string',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            // Spesies (GeneralReportSpecies)
            'category' => 'nullable|string',
            'species_latin_name' => 'nullable|string',
            'species_family' => 'nullable|string',
            'species_age' => 'nullable|string',
            'population' => 'nullable|integer',
            // Diagnosis awal (GeneralReportDiagnosis)
            'dead_sign' => 'nullable|array',
            'dead_sign_other' => 'nullable|string',
            'live_sign' => 'nullable|array',
            'live_sign_other' => 'nullable|string',
            'chronological' => 'nullable|string',
            'sampling' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'temporary_diagnosis_id' => 'nullable|integer',
            // Laporan masyarakat yang ditindaklanjuti jadi laporan resmi (opsional)
            'id_laporan_masyarakan' => 'nullable|integer',
            // Foto (base64), bisa berisi string base64 atau {id, deleted:true} untuk hapus
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
                'client_id', //
                'protected_species'
            ]);
            $generalreportsource = null;
            if (array_key_exists('id', $params)) {
                $generalreportsource = GeneralReportSource::where('status', 1)->where('id', $params['id'])->first();
                $generalreportverification = GeneralReportVerification::where('id', $params['id'])->first();
                if (!$generalreportsource) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }


                $this->saveReporter($request, $generalreportsource);
                $this->saveLocation($request, $generalreportsource);
                $this->saveSpecies($request, $generalreportsource);
                $this->saveDiagnoses($request, $generalreportsource);
                // $this->saveAck($request, $generalreportsource);
                // $this->saveVerify($request, $generalreportsource);
                // $this->saveLab($request, $generalreportsource);
                $this->saveMediaField($request, $generalreportsource, 'file');
            } else {
                // set terakhir disini
                // $params['code'] = $this->generateKode($upt);;
                $params['report_code'] = $this->generateKode($upt);
                $params['training'] = $currentUser->training_mode;
                $params['creator'] = $user->id;
                $params['user_id'] = $user->id;
                $params['upt_id'] = $request->upt_id ?? $user->upt_id;
                $generalreportsource = GeneralReportSource::create($params);
                // assign new value
                $params['id'] = $generalreportsource->id;

                //  parameter samakan
                $this->GenerateReportReporter($request, $params, $generalreportsource, $upt, $currentUser);
                $this->GenerateReportLocation($request, $params, $generalreportsource, $upt);
                $this->GenerateReportSpecies($request, $params, $generalreportsource);
                $this->GenerateReportDiagnosis($request, $generalreportsource);
                $this->saveMediaField($request, $generalreportsource, 'file');
                $this->updateUserActivity();

                $this->GenerateReportCommunity($request, $generalreportsource);


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
            }
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

    public function GenerateReportCommunity($request, $generalreportsource)
    {
        if ($request->id_laporan_masyarakan) {
            $masyarakat = CommunityReport::where('id', $request->id_laporan_masyarakan)->first();
            $masyarakat->id_laporan = $generalreportsource->id;
            $masyarakat->reporter_id = $request->reporter_id;
            $masyarakat->save();
        }
    }

    /**
     * Hapus laporan kasus (soft delete)
     *
     * Tidak menghapus record secara fisik, hanya set `status = 0` sehingga
     * laporan tidak lagi muncul di listing.
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();
            $generalreportsource = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $generalreportsource = GeneralReportSource::where('status', 1)->where('id', $params['id'])->first();
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

    public function saveReporter(Request $request, $reporter)
    {
        $params = $request->only([
            'id',
            'user_id',
            'name',
            'gender',
            'occupation',
            'phone',
            'address',
            'case_found',
            'client_id'
        ]);
        if (count($params) > 0) {
            // if ($params['id']) {
            //     $generalReporter = GeneralReportReporter::where('id', $params['id'])->first();
            // } elseif ($params['client_id']) {
            // }
            if ($params['id']) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
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
                    'user_id' => $params['user_id'],
                    'name' => $params['name'],
                    'gender' => $params['gender'],
                    'occupation' => $params['occupation'],
                    'phone' => $params['phone'],
                    'address' => $params['address'],
                    'case_found' => $params['case_found'],
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
            'id',
            'upt_id',
            'upt_type',
            'upt_name',
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
            'client_id'
        ]);
        if (count($params) > 0) {
            if ($params['id']) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
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
                    'upt_id' => $params['upt_id'] ?? null,
                    'upt_type' => $params['upt_type'] ?? null,
                    'upt_name' => $params['upt_name'] ?? null,
                    'location_name' => $params['location_name'] ?? null,
                    'conservation_type' => $params['conservation_type'] ?? null,
                    'insitu_conservation' => $params['insitu_conservation'] ?? null,
                    'insitu_other' => $params['insitu_other'] ?? null,
                    'exsitu_conservation' => $params['exsitu_conservation'] ?? null,
                    'exsitu_other' => $params['exsitu_other'] ?? null,
                    'province_id' => $params['province_id'] ?? null,
                    'district_id' => $params['district_id'] ?? null,
                    'subdistrict_id' => $params['subdistrict_id'] ?? null,
                    'village_id' => $params['village_id'] ?? null,
                    'location_description' => $params['location_description'] ?? null,
                    'latitude' => $params['latitude'] ?? null,
                    'longitude' => $params['longitude'] ?? null,
                ]);
                return true;
            }
            $generalLocations = new GeneralReportLocation();
            $generaLocationArray = $generalLocations->toArray($params);
            $generalLocations->fill($generaLocationArray);
            $generalLocations->save();
            return true;
        }
    }

    public function saveSpecies(Request $request, $reporter)
    {
        $params = $request->only([
            'id',
            'category',
            'protected',
            'protected_species',
            'species_name',
            'species_latin_name',
            'species_family',
            'species_age',
            'population',
            'client_id'
        ]);
        if (count($params) > 0) {
            Log::error($params);
            if ($params['id']) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
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
                    'category' => $params['category'] ?? null,
                    'protected' => $params['protected'] ?? null,
                    'protected_species' => $params['protected_species'] ?? null,
                    'species_name' => $params['species_name'] ?? null,
                    'species_latin_name' => $params['species_latin_name'] ?? null,
                    'species_family' => $params['species_family'] ?? null,
                    'species_age' => $params['species_age'] ?? null,
                    'population' => $params['population'] ?? null,
                ]);
                return true;
            }

            $generalSpeciess = new GeneralReportSpecies();
            // $generalSpeciesArray = $generalSpeciess->toArray($params);
            // $generalSpeciess->fill($generalSpeciesArray);
            $generalSpeciess->category = $params['category'] ?? null;
            $generalSpeciess->protected = $params['protected'] ?? null;
            $generalSpeciess->protected_species = $params['protected_species'] ?? null;
            $generalSpeciess->species_name = $params['species_name'] ?? null;
            $generalSpeciess->species_latin_name = $params['species_latin_name'] ?? null;
            $generalSpeciess->species_age = $params['species_age'] ?? null;
            $generalSpeciess->population = $params['population'] ?? null;
            $generalSpeciess->save();
            return true;
        }
    }

    public function saveDiagnoses(Request $request, $reporter)
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
            'live_sign',
            'client_id'
        ]);
        if (count($params) > 0) {
            if ($params['id']) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
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

                GeneralReportDiagnosis::where('id', $generalReportSource->id)->update([
                    'report_date' => $params['report_date'] ?? null,
                    'dead' => $params['dead'] ?? null,
                    'dead_sign' => $params['dead_sign'] ?? null,
                    'dead_sign_other' => $params['dead_sign_other'] ?? null,
                    'live' => $params['live'] ?? null,
                    'live_sign' => $params['live_sign'] ?? null,
                    'live_sign_other' => $params['live_sign_other'] ?? null,
                    'chronological' => $params['chronological'] ?? null,
                    'sampling' => $params['sampling'] ?? null,
                    'follow_up' => $params['follow_up'] ?? null,
                    'diagnosis' => $params['diagnosis'] ?? null,
                    'temporary_diagnosis_id' => $params['temporary_diagnosis_id'] ?? null,
                ]);
                return true;
            }

            $generalDiagnoses = new GeneralReportDiagnosis();
            $generalReportDiagnosesArray = $generalDiagnoses->toArray($params);
            $generalDiagnoses->fill($generalReportDiagnosesArray);
            $generalDiagnoses->save();
            return true;
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

    /**
     * Verifikasi dokter hewan
     *
     * Diisi oleh dokter hewan (`user.is_doctor`) untuk laporan yang sudah dibuat.
     * Jika penyakit yang dipilih (`temporary_disease_id`) memiliki `priority = true`,
     * memicu push notification ke Pusat/kepala UPT. Foto dikirim base64 di
     * `file_verification[]`.
     */
    public function saveVerify(Request $request)
    // public function saveVerify(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'client_id' => 'nullable|string',
            'verified_date' => 'nullable|date',
            'verified' => 'nullable|boolean',
            'verification' => 'nullable|string',
            'temporary_disease_id' => 'nullable|integer',
            'sampling' => 'nullable|string',
            'action' => 'nullable|string',
            'doctor_information' => 'nullable|string',
            'involved_doctors' => 'nullable|array',
            'file_verification' => 'nullable|array',
        ]);

        $params = $request->only([
            "id",
            // 'doctor_name',
            // 'doctor_occupation',
            'verified_date',
            'verified',
            'verification',
            'temporary_disease_id',
            'sampling',
            'action',
            'doctor_information',
            'involved_doctors',
            'client_id'
        ]);
        if (count($params) > 0) {
            if (array_key_exists('id', $params)) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            }
            if ($generalReportSource) {
                $generalVerify = GeneralReportVerification::where('id', $generalReportSource->id)->first();
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

                    $generalreportverification = GeneralReportVerification::where('id', $generalReportSource->id)->update([
                        // 'doctor_name' => $params['doctor_name'] ?? null,
                        // 'doctor_occupation' => $params['doctor_occupation'] ?? null,
                        'verified_date' => $params['verified_date'] ?? null,
                        'verified' => $params['verified'] ?? null,
                        'verification' => $params['verification'] ?? null,
                        'temporary_disease_id' => $params['temporary_disease_id'] ?? null,
                        'sampling' => $params['sampling'] ?? null,
                        'action' => $params['action'] ?? null,
                        'doctor_information' => $params['doctor_information'] ?? null,
                        'involved_doctors' => $params['involved_doctors'] ?? null,
                    ]);

                    $this->saveMediaField($request, $generalVerify, 'file_verification');
                    return [
                        'success' => true,
                        'data' => $generalreportverification
                    ];
                }
            }

            $generalreportverification = new GeneralReportVerification();
            // $generalVerifyArray = $generalVerify->toArray($params);
            // $generalreportverification->fill($params);
            $generalreportverification->id = $generalReportSource->id; // tambahan
            $generalreportverification->verified_date = $params['verified_date'] ?? null;
            $generalreportverification->verified = $params['verified'] ?? 0;
            $generalreportverification->verification = $params['verification'] ?? null;
            $generalreportverification->temporary_disease_id = $params['temporary_disease_id'] ?? null;
            $generalreportverification->sampling = $params['sampling'] ?? null;
            $generalreportverification->action = $params['action'] ?? null;
            $generalreportverification->doctor_information = $params['doctor_information'] ?? null;
            $generalreportverification->involved_doctors = $params['involved_doctors'] ?? null;
            $generalreportverification->save();

            $this->saveMediaField($request, $generalreportverification, 'file_verification');

            return [
                'success' => true,
                'data' => $generalreportverification
            ];
        } else {
            return true;
        }
    }

    /**
     * Hasil lab
     *
     * Menyimpan diagnosis final berdasarkan hasil pemeriksaan laboratorium
     * (`final_disease_id` merujuk ke master `Disease`).
     */
    public function saveLab(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'client_id' => 'nullable|string',
            'final_disease_id' => 'nullable|integer',
            'final_diagnosis' => 'nullable|string',
            'follow_up' => 'nullable|string',
        ]);

        $params = $request->only([
            'id',
            'final_disease_id',
            'final_diagnosis',
            'follow_up',
            'client_id'
        ]);
        if (count($params) > 0) {
            if (array_key_exists('id', $params)) {
                $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
            } else {
                $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
            }
            if ($generalReportSource) {
                $generalLab = GeneralReportLab::where('id', $generalReportSource->id)->first();
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

                    $lab = GeneralReportLab::where('id', $generalReportSource->id)->update([
                        'final_disease_id' => $params['final_disease_id'],
                        'final_diagnosis' => $params['final_diagnosis'],
                        'follow_up' => $params['follow_up']
                    ]);
                    // return true;
                    return [
                        'success' => true,
                        'data' => $lab
                    ];
                }
            }

            $lab = new GeneralReportLab();
            // $generalLabArray = $generalLab->toArray($params);
            $lab->fill($params);
            // $lab->id = $params['id'];
            $lab->id = $generalReportSource->id;
            $lab->save();

            return [
                'success' => true,
                'data' => $lab
            ];
        } else {
            return true;
        }
    }

    /**
     * Investigasi lapangan
     *
     * Diisi oleh tim investigasi jika laporan memerlukan tindak lanjut lapangan
     * (kunjungan, pengambilan bukti). Bukti dikirim di `evidence[]`, foto di
     * `file_investigation[]`.
     */
    public function saveInvestigation(Request $request)
    {
        // return $request->post();
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'client_id' => 'nullable|string',
                'investigation_date_from' => 'nullable|date',
                'investigation_date_to' => 'nullable|date',
                'inspection_method' => 'nullable|string',
                'evidence' => 'nullable|array',
                'follow_up_carried_out' => 'nullable|string',
                'additional_information' => 'nullable|string',
                'data_investigation' => 'nullable|array',
                'file_investigation' => 'nullable|array',
            ]);

            $params = $request->only([
                'id',
                'investigation_date_from',
                'investigation_date_to',
                'inspection_method',
                'evidence',
                'follow_up_carried_out',
                'additional_information',
                'data_investigation',
                'client_id'
            ]);

            if (count($params) > 0) {
                // return $params['id'];
                if (array_key_exists('id', $params)) {
                    $generalReportSource = GeneralReportSource::where('id', $params['id'])->first();
                } else {
                    $generalReportSource = GeneralReportSource::where('client_id', $params['client_id'])->first();
                }
                // if ($generalReportSource) {
                $investigations = GeneralReportInvestigation::where('id', $generalReportSource->id)->first();
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

                    $investigation = GeneralReportInvestigation::where('id', $generalReportSource->id)->update([
                        'investigation_date_from' => $params['investigation_date_from'],
                        'investigation_date_to' => $params['investigation_date_to'],
                        'inspection_method' => $params['inspection_method'],
                        'evidence' => $params['evidence'],
                        'follow_up_carried_out' => $params['follow_up_carried_out'],
                        'additional_information' => $params['additional_information'],
                        'data_investigation' => $params['data_investigation']
                    ]);

                    $this->saveMediaField($request, $investigations, 'file_investigation');
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
                $investigation1->id = $generalReportSource->id;
                $investigation1->investigation_date_from = $params['investigation_date_from'] ?? null;
                $investigation1->investigation_date_to = $params['investigation_date_to'] ?? null;
                $investigation1->inspection_method = $params['inspection_method'] ?? null;
                $investigation1->evidence = $params['evidence'] ?? null;
                $investigation1->follow_up_carried_out = $params['follow_up_carried_out'] ?? null;
                $investigation1->additional_information = $params['additional_information'] ?? null;
                $investigation1->data_investigation = $params['data_investigation'] ?? null;
                $investigation1->save();
                $investigation2 = GeneralReportInvestigation::where('id', $generalReportSource->id)->first();

                $this->saveMediaField($request, $investigation2, 'file_investigation');
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

        $bulan = strlen($month);
        $data = [
            'user_id' => $user->id,
            'month' => $bulan == 1 ? '0' . $month : $month,
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
            if ($generalreportsource->species_id) {
                $species = Species::where('id', $generalreportsource->species_id)->first();
            } else {
                $species = Species::where('id', $params['protected_species'])->first();
            }

            $generalSpecies = new GeneralReportSpecies();
            $generalSpecies->id = $generalreportsource->id;
            $generalSpecies->protected_species = $generalreportsource->species_id;
            $generalSpecies->category = $species->category ?? null;
            $generalSpecies->species_name = $species->name ?? null;
            $generalSpecies->species_latin_name = $species->latin_name ?? null;
            $generalSpecies->species_family = $species->family ?? null;
            $generalSpecies->protected_species = $post['protected_species'] ?? null;
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

        if ($request->reporter_id) {
            $user = User::where('id', $request->reporter_id)->first();

            $reporter = new GeneralReportReporter();
            $reporter->id = $params['id'];
            $reporter->name = $user->name ?? null;
            $reporter->gender = $user->gender ?? null;
            $reporter->occupation = $user->occupation ?? null;
            $reporter->phone = $user->phone ?? null;
            $reporter->address = $upt->address ?? $user->address ?? null;
            $reporter->case_found = true;
            $reporter->save();
        } else {
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
    }
    /**
     * Simpan foto base64 ke koleksi media (helper internal)
     *
     * Dipakai secara internal oleh `save()`, `saveVerify()`, `saveLab()`, dan
     * `saveInvestigation()` untuk menyimpan foto base64 (array `{file: [...]}`)
     * ke koleksi media terkait. Bukan endpoint route — untuk upload foto via
     * multipart/form-data (endpoint `general-reports/uploadPhoto`), lihat
     * method `uploadFile(Request $request)` di bawah.
     */
    public function saveMediaField($request, $data, $only)
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
            Log::error($this->controllerName . '-saveMediaField: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Upload foto laporan umum (multipart/form-data)
     *
     * Foto ditambahkan ke koleksi media yang sudah ada (append), bukan
     * menimpa, karena endpoint ini dipanggil berulang di tahap Investigasi,
     * Verifikasi Dokter Hewan, dan Investigasi Lanjutan untuk `id` yang sama.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'required|file',
            ]);

            $id = $request->input('id');

            $generalreportsource = GeneralReportSource::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            if (!$generalreportsource) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $generalreportsource->addMediaFromRequest('file')
                ->toMediaCollection();

            $generalreportsource = GeneralReportSource::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $generalreportsource
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

    public function tampilinSemua()
    {
        $query = GeneralReportSource::with('investigation', 'diagnoses.disease', 'lab', 'verification.disease', 'reporter', 'species', 'specieses', 'media', 'user', 'upt', 'location')->where('status', 1)->get();
        return $query;
    }


    /**
     * Kirim notifikasi satwa dilindungi
     *
     * Mengirim push notification (FCM) "Laporan Kasus Satwa di Lindungi" ke user
     * di UPT-type yang sama, UPT `PUSAT`, dan kepala UPT — hanya jika `protected`
     * bernilai `true`.
     */
    public function sendNotifikasiSatwaDiLindungi(Request $request)
    {
        $request->validate([
            'protected' => 'nullable|boolean',
        ]);

        $params = $request->post();
        $SERVER_API_KEY = env('APP_SERVER_API_KEY');

        //notifikasi pusat -> hewan di linduungi -> protected
        if ($params['protected'] && $params['protected'] == true) {
            //level 4 petugas
            $user = User::where('upt_type', auth()->user()->upt_type)
                ->orWhere('upt_type', 'PUSAT')
                ->orWhere('heads_upt', 1)
                // ->orWhereNotNull('devices')
                // ->pluck('devices')
                ->get();

            $token = [];
            foreach ($user as $key => $value) {
                if (
                    $value->user_level || $value->heads_upt
                ) { //auth()->user()->upt_id
                    array_push(
                        $token,
                        $value->devices
                    );
                }
            };

            // $firebasePETUGAS = User::where('user_level', 2)
            //     ->orWhere('user_level', 3)
            //     ->orWhere('user_level', 4)
            //     ->orWhere('heads_upt', 1)
            //     ->where('status', 1)
            //     // ->pluck('devices')
            //     ->all();

            $subject = 'SehatSatli';
            $body = 'Laporan Kasus Satwa di Lindungi';
            $dataJson = "Laporan Kasus Satwa di Lindungi";

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
                'registration_ids' => $token,
                'notification' => [
                    'title' => $subject,
                    'body' => $body,
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
        return true;
    }

    /**
     * Kirim notifikasi penyakit prioritas
     *
     * Mengirim push notification (FCM) "Laporan Penyakit Prioritas" ke user di
     * UPT-type yang sama, UPT `PUSAT`, dan kepala UPT — hanya jika `Disease`
     * yang dirujuk `temporary_disease_id` memiliki `priority = true`.
     */
    public function sendNotificationDiseasePriority(Request $request)
    {
        $request->validate([
            'temporary_disease_id' => 'required|integer',
        ]);

        $params = $request->post();
        $SERVER_API_KEY = env('APP_SERVER_API_KEY');

        //notifikasi pusat -> hewan di linduungi -> protected
        $disease = Disease::where('id', $params['temporary_disease_id'])->first();
        // return $disease;
        if ($disease->priority === true) {
            //level 4 petugas
            $user = User::where('upt_type', auth()->user()->upt_type)
                ->orWhere('upt_type', 'PUSAT')
                ->orWhere('heads_upt', 1)
                ->get();
            // return $user;
            $token = [];
            foreach ($user as $key => $value) {
                if ($value->user_level || $value->heads_upt) { //auth()->user()->upt_id
                    array_push($token, $value->devices);
                }
            };

            // $firebasePETUGAS = User::where('user_level', 2)
            //     ->orWhere('user_level', 3)
            //     ->orWhere('user_level', 4)
            //     ->orWhere('heads_upt', 1)
            //     ->where('status', 1)
            //     ->pluck('devices')
            //     ->all();

            $subject = 'SehatSatli';
            $body = 'Laporan Penyakit Prioritas';
            $dataJson = "Laporan Penyakit Prioritas";

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


            // return $token;
            $data = array(
                'registration_ids' => $token,
                'notification' => [
                    'title' => $subject,
                    'body' => $body,
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

        return true;
    }
}
