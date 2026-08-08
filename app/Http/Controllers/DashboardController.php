<?php

namespace App\Http\Controllers;

use App\Models\GeneralReportDiagnosis;
use App\Models\GeneralReportLab;
use App\Models\GeneralReportLocation;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralReportSource;
use App\Models\GeneralReportReporter;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{

    private $controllerName = 'DashboardController';

    public function get(Request $request)
    {
        try {
            $generalreportdiagnosis = GeneralReportSource::select(
                // DB::raw("count(id) as data"),
                DB::raw("DATE_FORMAT(report_date, '%Y%m') new_date"),
                DB::raw('YEAR(report_date) year, MONTH(report_date) month')
            )->groupBy('report_date')
                ->orderBy('new_date')
                ->get();

            return [
                'success' => true,
                'data' => $generalreportdiagnosis
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }


    public function getUserActivities(Request $request)
    {
        try {
            $officer = User::with(['upt'])->get();

            return [
                'success' => true,
                'data' => $officer
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }


    public function getSuspecDisease(Request $request)
    {
        try {
            $suspecDisease = GeneralReportLocation::with(['report', 'lab'])->get();

            return [
                'success' => true,
                'data' => $suspecDisease
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function getNonDisease(Request $request)
    {
        try {
            $suspectNonDisease = GeneralReportLocation::with(['report'])->get();

            return [
                'success' => true,
                'data' => $suspectNonDisease
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error, cannot load data'
            ];
        }
    }

    public function getDiagnosis(Request $request)
    {
        try {
            $generalreportdiagnosis = GeneralReportDiagnosis::with(['species', 'report'])->get();

            return [
                'success' => true,
                'data' => $generalreportdiagnosis
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function getLab(Request $request)
    {
        try {
            $generalreportlab = GeneralReportLab::with(['species', 'location', 'diagnosis', 'location.province', 'location.district', 'location.subdistrict', 'location.village'])->get();

            return [
                'success' => true,
                'data' => $generalreportlab
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function getLabOne(Request $request)
    {
        try {
            $params = $request->post();

            $generalreportlab = GeneralReportLab::with(['species', 'location', 'location.province', 'location.district', 'location.subdistrict', 'location.village'])->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $generalreportlab
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
     * Titik lokasi laporan (peta)
     *
     * Daftar laporan dengan koordinat valid (`latitude`/`longitude` != 0),
     * di-scope ke `training` sesuai `user.training_mode`. Dipakai untuk
     * menampilkan pin di peta.
     */
    public function dashboard()
    {

        $auth = auth()->user();

        $general = DB::table('general_report_sources as src')
            ->leftJoin('general_report_species as spc', 'src.id', '=', 'spc.id')
            ->leftJoin('general_report_diagnoses as diagnos', 'src.id', '=', 'diagnos.id')
            ->leftJoin('general_report_doctor_verifications as verif', 'src.id', '=', 'verif.id')
            ->leftJoin('general_report_locations as loc', 'src.id', '=', 'loc.id')
            ->select(
                'src.id as id_sources',
                'spc.species_name',
                'src.training',
                'diagnos.id as id_diagnosis',
                'verif.id as id_doctor',
                'loc.latitude',
                'loc.longitude',
                'loc.location_name',
                'src.protected',
                'diagnos.temporary_diagnosis_id as diagnoses'
            )
            ->where('loc.latitude', '!=', 0)
            ->where('loc.longitude', '!=', 0)
            ->where('src.training', $auth->training_mode)
            ->get();

        return [
            'success' => true,
            'data' => $general
        ];
    }

    public function report_satwa_liar()
    {
        $satwa_liar = DB::table('general_report_sources as src')
            ->leftJoin('general_report_species as spc', 'src.id', '=', 'spc.id')
            ->leftJoin('general_report_diagnoses as diagnos', 'src.id', '=', 'diagnos.id')
            ->leftJoin('general_report_locations as loc', 'src.id', '=', 'loc.id')
            ->get();

        return $satwa_liar;
    }

    /**
     * Rekap laporan per bulan (mobile/app)
     *
     * Jumlah laporan per bulan (`training` sesuai `user.training_mode`)
     * digabung dengan jumlah aktivitas user per bulan (`user_activities`).
     */
    public function report_perbulan()
    {
        $date = Carbon::now();
        $years = $date->year;
        $auth = auth()->user();

        $generalreportdiagnosis = DB::table('general_report_sources')->select(
            DB::raw("DATE_FORMAT(report_date, '%Y-%M') new_date_name"),
            DB::raw("DATE_FORMAT(report_date, '%Y%m') new_date"),
            DB::raw('YEAR(report_date) year, MONTH(report_date) month'),
            DB::raw('training'),
            DB::raw('count(id) as total_report')
        )
            //->whereRaw('report_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
            ->groupBy('new_date')
            ->orderBy('new_date')
            ->where('training', $auth->training_mode)
            ->get();

        $user_active_report = DB::table('user_activities')->select(
            DB::raw("CONCAT(year, month) as new_date"),
            DB::raw("count(id) as total_report"),
        )
            // ->whereYear('report_date', '2017')
            ->groupBy("new_date")
            ->orderBy("new_date")
            ->get();

        $new_date_name = [];
        $new_date = [];
        $year = [];
        $month = [];
        $total_report = [];

        // return [$generalreportdiagnosis, $user_active_report];

        foreach ($generalreportdiagnosis as $key => $items) {

            foreach ($user_active_report as $key => $active) {
                if ($items->new_date === $active->new_date) {
                    $new_date_name[] = $items->new_date_name;
                    $year[] = $items->year;
                    $month[] = $items->month;
                    $new_date[] = $items->new_date;
                    $total_report[] = $items->total_report + $active->total_report;
                }

                // if ($items->new_date !== $active->new_date) {
                // $new_date_name[] = $items->new_date_name;
                // $year[] = $items->year;
                // $month[] = $items->month;
                // $new_date[] = $items->new_date;
                // $total_report[] = $items->total_report;
                // }
            }
        }

        // $new_date_name = json_decode(json_encode($new_date_name), FALSE);
        // $new_date = json_decode(json_encode($new_date), FALSE);
        // $year = json_decode(json_encode($year), FALSE);
        // $month = json_decode(json_encode($month), FALSE);
        // $total_report = json_decode(json_encode($total_report), FALSE);

        return
            [
                'success' => true,
                'data' => $generalreportdiagnosis,
                // 'data' => [
                //     "new_date_name" => $new_date_name,
                //     'new_date' => $new_date,
                //     "year" => $year,
                //     "month" => $month,
                //     'total_report' => $total_report,
                // ],
                'year' => $years,
            ];
    }

    public function suspek_penyakit()
    {
        // $auth = auth()->user();
        $suspec = DB::table('general_report_sources as src')
            ->leftJoin('general_report_diagnoses as diagnos', 'src.id', '=', 'diagnos.id')
            ->where('diagnos.temporary_diagnosis_id', 1)
            // ->where('src.training', $auth->training_mode)
            ->get();

        return $suspec;
    }

    public function suspek_non_penyakit()
    {
        $auth = auth()->user();
        $non_suspec = DB::table('general_report_sources as src')
            ->leftJoin('general_report_diagnoses as diagnos', 'src.id', '=', 'diagnos.id')
            ->where('diagnos.temporary_diagnosis_id', 2)
            ->where('src.training', $auth->training_mode)
            ->get();

        return $non_suspec;
    }

    private function filterByUpt($current_user, $query)
    {
        if ($current_user->upt_id) {
            if ($current_user->upt_id > 2) {
                $query->where('upt_id', $current_user->upt_id);
            }
        }
        return $query;
    }

    private function filterByUsers($current_user, $query, $users)
    {
        if ($current_user->upt_id) {
            if ($current_user->upt_id > 2) {
                $query->whereIn('user_id', $users->pluck('id'));
            }
        }
        return $query;
    }

    /**
     * Matriks keaktifan user per bulan
     *
     * Untuk tiap user (di-scope ke UPT user login jika `upt_id > 2`) dan tiap
     * periode bulan/tahun dalam 1 tahun terakhir, menandai `true`/`false`
     * apakah user tersebut punya `UserActivity` pada periode itu.
     */
    public function userActive(Request $request)
    {
        // Get current user
        $current_user = User::find(auth()->user()->id);

        $limit = Carbon::now()->addYear(-1)->format("Y-m");

        $now = Carbon::now()->format("Y-m");

        $users = $this->filterByUpt($current_user, User::where('status', 1))->get();

        $all_activities = $this->filterByUsers(
            $current_user,
            UserActivity::whereRaw("CONCAT(`year`, '-', `month`) >= ? && CONCAT(`year`, '-', `month`) <= ?", [$limit, $now])
                ->orderByRaw("CONCAT(`year`, '-', `month`) asc"),
            $users
        )->get();

        $periods = $this->filterByUsers(
            $current_user,
            UserActivity::select('month', 'year')
                ->whereRaw("CONCAT(`year`, '-', `month`) >= ? && CONCAT(`year`, '-', `month`) <= ?", [$limit, $now])
                ->orderByRaw("CONCAT(`year`, '-', `month`) asc")
                ->distinct(),
            $users
        )->get();

        $result = [];

        foreach ($users as $key => $user) {
            $user_activities = [];

            foreach ($periods as $key => $period) {
                $activity = $all_activities
                    ->where('user_id', $user->id)
                    ->where('month', $period->month)
                    ->where('year', $period->year)
                    ->first();
                $user_activities[$period->getDate()] = $activity ? true : false;
            }

            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                ...$user_activities
            ];
        }

        return [
            'success' => true,
            'data' => [
                'activities' => $result,
                'periods' => $periods
            ],
        ];
    }

    /**
     * Rekap laporan per bulan (web, 1 tahun terakhir)
     */
    public function report_perbulanWeb()
    {
        $date = Carbon::now();
        $years = $date->year;

        $generalreportdiagnosisCount = DB::table('general_report_sources')->select(
            DB::raw("DATE_FORMAT(report_date, '%M') month_name"),
            DB::raw("DATE_FORMAT(report_date, '%Y-%M') new_date_name"),
            DB::raw("DATE_FORMAT(report_date, '%Y%m') new_date"),
            DB::raw('YEAR(report_date) year, MONTH(report_date) month'),
            DB::raw('count(id) as total_report')
        )
            ->whereRaw('report_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
            ->orderBy('new_date')
            // ->whereYear('report_date', $years)
            ->get();

        $generalreportdiagnosis = DB::table('general_report_sources')->select(
            DB::raw("DATE_FORMAT(report_date, '%M') month_name"),
            DB::raw("DATE_FORMAT(report_date, '%Y-%M') new_date_name"),
            DB::raw("DATE_FORMAT(report_date, '%Y%m') new_date"),
            DB::raw('YEAR(report_date) year, MONTH(report_date) month'),
            DB::raw('count(id) as total_report')
        )
            ->whereRaw('report_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
            ->groupBy('new_date')
            ->orderBy('new_date')
            // ->whereYear('report_date', $years)
            ->get();

        // return $generalreportdiagnosis;

        $user_active_report = DB::table('user_activities')->select(
            DB::raw("CONCAT(year, month) as new_date"),
            DB::raw("count(id) as total_report"),
        )
            // ->whereYear('report_date', $years)
            ->groupBy("new_date")
            ->orderBy("new_date")
            ->get();

        return
            [
                'success' => true,
                'data' => $generalreportdiagnosis,
                'year' => $years,
                'total' => $generalreportdiagnosisCount[0]->total_report
            ];
    }

    /**
     * Grafik suspek penyakit per provinsi (web)
     *
     * Jumlah laporan dengan `temporary_diagnosis_id = 1` (suspek penyakit),
     * dikelompokkan per provinsi, plus total 1 tahun terakhir.
     */
    public function suspekPenyakit()
    {
        $date = Carbon::now();
        $years = $date->year;


        $data = DB::table('general_report_sources as grs')
            ->leftJoin('general_report_diagnoses as grd', 'grd.id', '=', 'grs.id')
            ->leftJoin('general_report_locations as grl', 'grl.id', '=', 'grs.id')
            ->leftJoin('provinces as p', 'p.id', '=', 'grl.province_id')
            ->select(
                'grd.temporary_diagnosis_id',
                'p.name',
                'grs.report_date',
                DB::raw('count(grs.id) as total_report')
            )
            ->groupBy('name')
            ->where('temporary_diagnosis_id', 1)
            ->where('name', '!=', null)
            // ->whereYear('grs.report_date', $years)
            ->get();

        $count = DB::table('general_report_diagnoses')
            ->where('temporary_diagnosis_id', 1)
            ->whereRaw('report_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
            ->count();
        return [
            'grafikSuspek' => $data,
            'counSuspek' => $count
        ];
    }

    /**
     * Grafik suspek non-penyakit per provinsi (web)
     *
     * Jumlah laporan dengan `temporary_diagnosis_id = 2` (suspek non-penyakit),
     * dikelompokkan per provinsi, plus total 1 tahun terakhir.
     */
    public function nonSuspekPenyakit()
    {
        $date = Carbon::now();
        $years = $date->year;

        $data = DB::table('general_report_sources as grs')
            ->leftJoin('general_report_diagnoses as grd', 'grd.id', '=', 'grs.id')
            ->leftJoin('general_report_locations as grl', 'grl.id', '=', 'grs.id')
            ->leftJoin('provinces as p', 'p.id', '=', 'grl.province_id')
            ->select(
                'grd.temporary_diagnosis_id',
                'p.name',
                'grs.report_date',
                DB::raw('count(grs.id) as total_report')
            )
            ->groupBy('name')
            ->where('temporary_diagnosis_id', 2)
            ->where('name', '!=', null)
            // ->whereYear('grs.report_date', $years)
            ->get();

        $count = DB::table('general_report_diagnoses')
            ->where('temporary_diagnosis_id', 2)
            ->whereRaw('report_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
            ->count();

        return [
            'grafikNonSuspek' => $data,
            'counNonSuspek' => $count
        ];
    }
}
