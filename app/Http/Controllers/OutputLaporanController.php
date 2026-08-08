<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralReportLab;
use App\Models\GeneralReportSource;
use App\Models\GeneralReportLocation;
use App\Models\GeneralReportDiagnosis;
use App\Models\UPT;
use App\Models\User;
use App\Models\UserActivity;

use Carbon\Carbon;


class OutputLaporanController extends Controller
{
    /**
     * Rekap hasil lab
     *
     * Seluruh `GeneralReportLab` beserta spesies, lokasi, diagnosis, dan dokter
     * terkait. Dipakai untuk laporan/dashboard.
     */
    public function lab(Request $request)
    {
        try {
            $lab = GeneralReportLab::with(['species', 'location', 'diagnosis', 'doctor', 'location.province', 'location.district', 'location.subdistrict', 'location.village'])->get();

            return [
                'success' => true,
                'data' => $lab
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Rekap kasus suspek non-penyakit
     *
     * ⚠️ Nama method menyesatkan: filternya `temporary_diagnosis_id = 2`
     * yaitu kode untuk "Suspek Non Penyakit" (bukan "Suspek Penyakit").
     */
    public function suspekPenyakit(Request $request)
    {
        try {
            $suspek = DB::table('general_report_sources as src')
                ->leftJoin('general_report_locations as location', 'src.id', '=', 'location.id')
                ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->leftJoin('provinces', 'location.province_id', '=', 'provinces.id')
                ->leftJoin('general_report_species as species', 'src.id', '=', 'species.id')
                ->where('diagnoses.temporary_diagnosis_id', 2)
                ->where('location.province_id', '!=', 0)
                ->get();

            return [
                'success' => true,
                'data' => $suspek
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Rekap kasus suspek penyakit
     *
     * ⚠️ Nama method menyesatkan: filternya `temporary_diagnosis_id = 1`
     * yaitu kode untuk "Suspek Penyakit" (bukan "Non Penyakit").
     */
    public function nonPenyakit(Request $request)
    {
        try {
            $nonPenyakit = DB::table('general_report_sources as src')
                ->leftJoin('general_report_locations as location', 'src.id', '=', 'location.id')
                ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->leftJoin('provinces', 'location.province_id', '=', 'provinces.id')
                ->leftJoin('general_report_species as species', 'src.id', '=', 'species.id')
                ->where('diagnoses.temporary_diagnosis_id', 1)
                ->where('location.province_id', '!=', 0)
                ->get();

            return [
                'success' => true,
                'data' => $nonPenyakit
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error, cannot load data'
            ];
        }
    }

    /**
     * Ringkasan jumlah laporan per kategori
     *
     * Menghitung jumlah laporan: tidak ada kasus, suspek non penyakit
     * (`temporary_diagnosis_id = 2`), suspek penyakit (`= 1`), dan total.
     */
    public function rincianLaporan(Request $request)
    {
        // $user = auth()->user();
        // $userActive = UserActivity::where('user_id', auth()user()->id)->first();
        try {
            //
            $not_kasus = DB::table('general_report_sources as src')
                ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->where('diagnoses.temporary_diagnosis_id', null)
                ->get();

            $suspek = DB::table('general_report_sources as src')
                ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->where('diagnoses.temporary_diagnosis_id', 1)
                ->get();

            $nonPenyakit = DB::table('general_report_sources as src')
                ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->where('diagnoses.temporary_diagnosis_id', 2)
                ->get();

            return [
                "success" => true,
                "data" =>  [
                    'Tidak Ada Kasus' => count($not_kasus),
                    'Suspek Non Penyakit' => count($nonPenyakit),
                    'Suspek Penyakit' => count($suspek),
                    'Total' => count($not_kasus) + count($nonPenyakit) + count($suspek)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error, cannot load data'
            ];
        }
    }

    /**
     * List seluruh UPT (tanpa scope)
     */
    public function UPT(Request $request)
    {
        try {
            $upt = UPT::get();

            return [
                'success' => true,
                'data' => $upt
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error, cannot load data'
            ];
        }
    }

    /**
     * Rekap suspek penyakit per UPT
     *
     * Jumlah laporan dengan `temporary_diagnosis_id = 1` (suspek penyakit),
     * dikelompokkan per tipe & nama UPT.
     */
    public function getSuspek(Request $request)
    {

        $suspek = DB::table('upts')
            ->join('general_report_sources as src', 'upts.id', '=', 'src.upt_id')
            ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
            ->select(
                DB::raw('COUNT(temporary_diagnosis_id) total'),
                DB::raw('upts.type'),
                DB::raw('upts.name')
            )
            ->where('diagnoses.temporary_diagnosis_id', '=', 1)
            ->groupBy('upts.type')
            ->groupBy('upts.name')
            ->get();

        return [
            "success" => true,
            'data' => $suspek
        ];
    }

    /**
     * Rekap suspek non-penyakit per UPT
     *
     * Jumlah laporan dengan `temporary_diagnosis_id = 2` (suspek non-penyakit),
     * dikelompokkan per tipe & nama UPT.
     */
    public function getNonSuspek(Request $request)
    {

        $nonSuspek = DB::table('upts')
            ->join('general_report_sources as src', 'upts.id', '=', 'src.upt_id')
            ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
            ->select(
                DB::raw('COUNT(temporary_diagnosis_id) total'),
                DB::raw('upts.type'),
                DB::raw('upts.name')
            )
            ->where('diagnoses.temporary_diagnosis_id', '=', 2)
            ->groupBy('upts.type')
            ->groupBy('upts.name')
            ->get();

        return [
            "success" => true,
            'data' => $nonSuspek
        ];
    }

    /**
     * Rekap laporan belum terdiagnosis per UPT
     *
     * Jumlah laporan yang belum punya `temporary_diagnosis_id` sama sekali,
     * dikelompokkan per tipe & nama UPT.
     */
    public function getSuspekNotFound(Request $request)
    {
        $suspekNotFound = DB::table('upts')
            ->join('general_report_sources as src', 'upts.id', '=', 'src.upt_id')
            ->leftJoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
            ->select(
                DB::raw('COUNT(temporary_diagnosis_id) total'),
                DB::raw('upts.type'),
                DB::raw('upts.name')
            )
            ->where('diagnoses.temporary_diagnosis_id', null)
            ->groupBy('upts.type')
            ->groupBy('upts.name')
            ->get();

        return [
            'success' => true,
            'data' => $suspekNotFound
        ];
    }

    /**
     * Rekap petugas per UPT (kasus non-penyakit)
     */
    public function petugas(Request $request)
    {
        try {
            $petugas = DB::table('upts')
                ->leftJoin('users', 'users.upt_id', '=', 'upts.id')
                ->leftJoin('general_report_sources as src', 'upts.id', '=', 'src.upt_id')
                ->leftjoin('general_report_diagnoses as diagnoses', 'src.id', '=', 'diagnoses.id')
                ->select(
                    // DB::raw('COUNT(diagnoses.temporary_diagnosis_id) count'),
                    // DB::raw('diagnoses.id as diagnosa'),
                    DB::raw('upts.type'),
                    DB::raw('upts.name as name_upt'),
                    DB::raw('users.name')
                )
                ->groupBy('upts.type')
                ->groupBy('upts.name')
                ->groupBy('users.name')
                ->where('diagnoses.temporary_diagnosis_id', '=', 2)
                ->get();

            return [
                'success' => true,
                'data' => $petugas
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error, cannot load data'
            ];
        }
    }
}
