<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class UserActivityController extends Controller
{
    //
    //
    private $controllerName = 'UserActivityController';
    /**
     * Login
     */
    /**
     * List aktivitas user
     *
     * Rekap aktivitas bulanan (`UserActivity`) semua user — dipakai dashboard
     * monitoring keaktifan petugas.
     */
    public function getAll(Request $request)
    {
        try {
            $userinbox = UserActivity::where('status', 1)->get();

            return [
                'success' => true,
                'data' => $userinbox
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    /**
     * Heartbeat aktivitas bulanan
     *
     * Dipanggil app secara berkala (mis. saat buka app) untuk mencatat
     * `last_seen` user pada bulan berjalan, dan opsional menyimpan FCM device
     * token (`token`) jika user belum punya token tersimpan.
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'token' => 'nullable|string',
            ]);

            $user = auth()->user();
            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;

            $bulan = strlen($month);

            $user_activity = UserActivity::where('user_id', $user->id)
                ->where('month', $bulan == 1 ? '0' . $month : $month)
                ->where('year', $year)
                ->first();
            if ($user_activity) {
                $user_activity->last_seen = $now;
                $user_activity->save();
            } else {
                $data = [
                    'user_id' => $user->id,
                    'month' => $bulan == 1 ? '0' . $month : $month,
                    'year' => $year,
                    'last_seed' => $now
                ];
                UserActivity::create($data);
            }

            $token = $request->input('token', null);
            if ($token) {
                $currentUser = User::where('status', 1)
                    ->where('id', $user->id)->first();
                // if (!in_array($token, $currentUser->devices ? $currentUser->devices : [])) {
                if (!$currentUser->devices) {
                    // $devices = $currentUser->devices ?? '';
                    // array_push($devices, $token);
                    $currentUser->devices = $token;
                    $currentUser->save();
                }
            }


            return [
                'success' => true,
                'data' => null
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
        }
    }
}
