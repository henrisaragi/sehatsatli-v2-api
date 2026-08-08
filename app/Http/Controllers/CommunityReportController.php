<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use Illuminate\Http\Request;


class CommunityReportController extends Controller
{
    /**
     * List laporan masyarakat
     *
     * Mengembalikan seluruh laporan yang dikirim warga (`CommunityReport`),
     * beserta pelapor (`user`) dan laporan resmi terkait (`laporan.upt`) jika
     * sudah ditindaklanjuti petugas.
     */
    public function getAll(Request $request)
    {
        try {
            $data = CommunityReport::with('user', 'laporan.upt')->get();

            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Detail 1 laporan masyarakat
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();
            $data = CommunityReport::with('user', 'laporan.upt')->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buat/ubah laporan masyarakat
     *
     * Endpoint publik (tanpa login) yang dipakai warga untuk melaporkan kejadian
     * awam. Kirim `id` untuk mengubah laporan yang sudah ada. Laporan ini belum
     * menjadi kasus resmi — petugas dapat menindaklanjutinya lewat
     * `general-reports/save` (field `id_laporan_masyarakan` + `reporter_id`).
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string',
                'report_date' => 'nullable|date',
                'description' => 'nullable|string',
            ]);

            $params = $request->only([
                'id',
                'name',
                'report_date',
                'description'
            ]);

            if (array_key_exists('id', $params)) {
                $data = CommunityReport::where('id', $params['id'])->first();
                if (!$data) {
                    return [
                        'success' => false,
                        'message' =>  'Error cannot load data'
                    ];
                }

                $data->fill($params);
                $data->save($params);

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            $data = CommunityReport::create($params);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Hapus laporan masyarakat
     *
     * ⚠️ Menghapus record secara fisik (bukan soft delete seperti entitas lain).
     */
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();
            $data = CommunityReport::where('id', $params['id'])->first();
            $data->delete();

            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
