<?php

namespace App\Http\Controllers;

use App\Models\UPT;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class UPTController extends Controller
{
    //
    private $controllerName = 'UPTController';
    /**
     * Login
     */
    /**
     * List UPT
     *
     * Daftar unit kerja (`UPT`) yang aktif. Otomatis di-scope hanya ke UPT user
     * yang login jika `upt_id > 2`.
     */
    public function getAll(Request $request)
    {
        try {
            $current_user = User::find(auth()->user()->id);

            $query = UPT::where('status', 1);

            if ($current_user->upt_id) {
                if ($current_user->upt_id > 2) {
                    $query->where('id', $current_user->upt_id);
                }
            }

            $upt = $query->get();

            return [
                'success' => true,
                'data' => $upt
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
     * Detail 1 UPT
     *
     * Termasuk daftar user (`user`) dan laporan kasus (`generalReportSource`)
     * yang berada di UPT tersebut.
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();

            // Disini tambahkan with media
            $upt = UPT::with('media', 'user', 'generalReportSource.species', 'generalReportSource.specieses')->where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $upt
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
     * Buat/ubah UPT
     *
     * Kirim `id` untuk mengubah data yang sudah ada. `type`: PUSAT/KSDA/TN/LU/LK
     * (lihat `masters.options.upt_type`). `unit_id` adalah id UPT induk (mis.
     * Lembaga Khusus di bawah BKSDA).
     */
    public function save(Request $request)
    {
        $user = auth()->user();
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'status' => 'nullable|integer',
                'name' => 'nullable|string',
                'code' => 'nullable|string',
                'type' => 'nullable|string',
                'upt_type' => 'nullable|string',
                'unit_id' => 'nullable|integer',
                'address' => 'nullable|string',
                'latitude' => 'nullable',
                'longitude' => 'nullable',
                'province_id' => 'nullable|integer',
                'district_id' => 'nullable|integer',
                'subdistrict_id' => 'nullable|integer',
                'village_id' => 'nullable|integer',
                'conservation_type' => 'nullable|string',
                'insitu_conservation' => 'nullable|array',
                'insitu_other' => 'nullable|string',
                'exsitu_conservation' => 'nullable|array',
                'exsitu_other' => 'nullable|string',
                'province_name' => 'nullable|string',
                'upt_heads' => 'nullable|array',
            ]);

            $params = $request->only([
                'id',
                'creator',
                'updater',
                'status',
                'name',
                'code',
                'type',
                'upt_type',
                'unit_id', // parent id, misal Lembaga Khusus dibawah BKSDA
                'address',
                'status',
                'latitude',
                'longitude',
                'province_id',
                'district_id',
                'subdistrict_id',
                'village_id',
                'conservation_type',
                'insitu_conservation',
                'insitu_other',
                'exsitu_conservation',
                'exsitu_other',
                // 'lk_umum_khusus',
                'province_name',
                'upt_heads'
            ]);

            $upt = null;
            if (array_key_exists('id', $params)) {
                $upt = UPT::where('status', 1)->where('id', $params['id'])->first();
                if (!$upt) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                unset($params['id']);
                $params['updater'] = $user->id;
                $upt->fill($params);
                $upt->save();
            } else {
                $params['creator'] = $user->id;
                $upt = UPT::create($params);
            }

            return [
                'success' => true,
                'data' => $upt
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-save: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
            throw ($e);
        }
    }

    /**
     * Hapus UPT
     *
     * Soft delete (`status = 0`).
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();
            $upt = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $upt = UPT::where('status', 1)->where('id', $params['id'])->first();
            if (!$upt) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $upt->status = 0;
            $upt->save();

            return [
                'success' => true,
                'data' => $upt
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-delete: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot delete data"
            ];
        }
    }

    public function UptSpeciesUserReport()
    {
        try {
            $upt = User::with(['upt.species'])->where('status', 1)->get();

            return [
                'success' => true,
                'data' => $upt
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-delete: success=fasle; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error, data not found'
            ];
        }
    }
    /**
     * Upload foto UPT
     *
     * Menghapus semua foto lama lalu menyimpan file baru dari `file`
     * (multipart/form-data) sebagai foto tunggal UPT.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'required|file',
            ]);

            $id = $request->input('id');


            $upt = UPT::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            foreach ($upt->media as $id => $media) {
                $media->delete();
            }

            $upt->addMediaFromRequest('file')
                ->toMediaCollection();

            $upt = UPT::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $upt
            ];
        } catch (Exception $e) {
            Log::error($this->controllerName . '-uploadFile: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }
}
