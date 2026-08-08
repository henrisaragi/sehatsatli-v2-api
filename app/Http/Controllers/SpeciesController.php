<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class SpeciesController extends Controller
{
    //
    private $controllerName = 'SpeciesController';
    /**
     * Login
     */
    /**
     * List spesies
     *
     * Master data satwa/tumbuhan (`Species`) yang aktif, beserta laporan kasus
     * yang merujuknya (`source`).
     */
    public function getAll(Request $request)
    {
        try {
            $species = Species::with('media', 'source')->where('status', 1)->get();

            return response()->json([
                'success' => true,
                'data' => $species
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
     * Detail 1 spesies
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();

            // Disini tambahkan with media
            $species = Species::with('media', 'source', 'source.specieses')->where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $species
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
     * Buat/ubah spesies
     *
     * Kirim `id` untuk mengubah data yang sudah ada. `category`: 1=Hewan,
     * 2=Tumbuhan (lihat `masters.options.species_category`). `protected = true`
     * menandai spesies dilindungi — laporan kasus dengan spesies ini memicu
     * push notification ke Pusat.
     */
    public function save(Request $request)
    {
        $user = auth()->user();
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'status' => 'nullable|integer',
                'category' => 'nullable|integer',
                'code' => 'nullable|string',
                'name' => 'nullable|string',
                'latin_name' => 'nullable|string',
                'type' => 'nullable|string',
                'family' => 'nullable|string',
                'priority' => 'nullable|boolean',
                'endangered' => 'nullable|boolean',
                'protected' => 'nullable|boolean',
            ]);

            $params = $request->only([
                'id',
                'creator',
                'updater',
                'status',
                'category',
                'code',
                'name',
                'latin_name',
                'type',
                'family',
                'priority',
                'endangered',
                'protected'
            ]);

            $species = null;
            if (array_key_exists('id', $params)) {
                $species = Species::where('status', 1)->where('id', $params['id'])->first();
                if (!$species) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                unset($params['id']);
                $params['updater'] = $user->id;
                $species->fill($params);
                $species->save();
            } else {
                $params['creator'] = $user->id;
                Log::info($params);
                $species = Species::create($params);
            }

            return [
                'success' => true,
                'data' => $species
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
            throw ($e);
        }
    }

    /**
     * Hapus spesies
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
            $species = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $species = Species::where('status', 1)->where('id', $params['id'])->first();
            if (!$species) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $species->status = 0;
            $species->save();

            return [
                'success' => true,
                'data' => $species
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-delete: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot delete data"
            ];
        }
    }
    /**
     * Upload foto spesies
     *
     * Menghapus semua foto lama lalu menyimpan file baru dari `file`
     * (multipart/form-data) sebagai foto tunggal spesies.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'required|file',
            ]);

            $id = $request->input('id');

            $species = Species::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();


            foreach ($species->media as $id => $media) {
                $media->delete();
            }

            $species->addMediaFromRequest('file')
                ->toMediaCollection();

            $species = Species::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $species
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
