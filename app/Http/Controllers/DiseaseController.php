<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\GeneralReportVerification;
use App\Models\GeneralReportDiagnosis;
use App\Models\GeneralReportLab;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class DiseaseController extends Controller
{
    //
    private $controllerName = 'DiseaseController';
    /**
     * Login
     */
    /**
     * List penyakit
     *
     * Master data penyakit (`Disease`) yang aktif.
     */
    public function getAll(Request $request)
    {
        try {
            $diseases = Disease::where('status', 1)->get();
            return [
                'success' => true,
                'data' => $diseases
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
     * Detail 1 penyakit
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();

            // Disini tambahkan with media
            $disease = Disease::with('media')->where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $disease
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
     * Buat/ubah penyakit
     *
     * Kirim `id` untuk mengubah data yang sudah ada. `priority = true` membuat
     * kasus dengan penyakit ini memicu push notification ke Pusat saat
     * diverifikasi dokter. `zoonosis = true` menandai penyakit berpotensi
     * menular ke manusia.
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
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'symptom' => 'nullable|string',
                'symptom_details' => 'nullable|array',
                'treatment' => 'nullable|string',
                'treatment_details' => 'nullable|array',
                'priority' => 'nullable|boolean',
                'zoonosis' => 'nullable|boolean',
            ]);

            $params = $request->only([
                'id',
                'creator',
                'updater',
                'status',
                'name',
                'code',
                "short_description",
                'description',
                'symptom',
                'symptom_details',
                'treatment',
                'treatment_details',
                'priority',
                'zoonosis',
            ]);

            $disease = null;
            if (array_key_exists('id', $params)) {
                $disease = Disease::where('status', 1)->where('id', $params['id'])->first();
                if (!$disease) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                unset($params['id']);
                $params['updater'] = $user->id;
                $disease->fill($params);
                $disease->save();
            } else {
                $params['creator'] = $user->id;
                $disease = Disease::create($params);
            }

            return [
                'success' => true,
                'data' => $disease
            ];
        } catch (Exception $e) {
            // Log::error($this->controllerName . '-save: success=false; error=' . $e->getMessage());
            throw ($e);
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
        }
    }

    /**
     * Hapus penyakit
     *
     * Soft delete (`status = 0`). Ditolak jika penyakit sudah dirujuk di
     * verifikasi dokter, diagnosis, atau hasil lab pada laporan manapun.
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            // $params = $request->post();
            $params = $request->post();
            $disease = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $disease = Disease::where('status', 1)->where('id', $params['id'])->first();
            if (!$disease) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }


            // TODO: Aldi
            // Cek apakah penyakit sudah ada di general repoort dan tabel lainnya,
            // Jika ada, maka return false
            $GeneralReportVerification = GeneralReportVerification::where('temporary_disease_id', $disease->id)->first();
            $GeneralReportDiagnosis = GeneralReportDiagnosis::where('temporary_diagnosis_id', $disease->id)->first();
            $GeneralReportLab = GeneralReportLab::where('final_disease_id', $disease->id)->first();
            if ($GeneralReportVerification && $GeneralReportDiagnosis && $GeneralReportLab) {
                return [
                    'success' => false,
                    'message' => "Error, data "
                ];
            }



            $disease->status = 0;
            $disease->save();

            return [
                'success' => true,
                'data' => $disease
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-delete: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot delete data",
            ];
        }
    }
    /**
     * Upload foto penyakit
     *
     * Menghapus semua foto lama lalu menyimpan file baru dari `file`
     * (multipart/form-data) sebagai foto tunggal penyakit.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'required|file',
            ]);

            $id = $request->input('id');

            $disease = Disease::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();


            foreach ($disease->media as $id => $media) {
                $media->delete();
            }

            $disease->addMediaFromRequest('file')
                ->toMediaCollection();

            $disease = Disease::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $disease
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
