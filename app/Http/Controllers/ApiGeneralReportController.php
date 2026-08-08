<?php

namespace App\Http\Controllers;

use App\Models\GeneralReportSource;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Input;

class ApiGeneralReportController extends Controller
{
    /**
     * Export laporan kasus (integrasi eksternal)
     *
     * Format ringkas laporan (nama provinsi/kab/kec/desa, spesies, kontak
     * pelapor, hasil verifikasi/lab/investigasi) untuk dikonsumsi sistem
     * eksternal (dipanggil lewat `getApi()` di bawah). Filter: `tahun` (paling
     * diprioritaskan), atau rentang `tgl_buat_laporan`..`tgl_akhir_laporan`,
     * atau tanpa filter (semua data, dibatasi `limit`).
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'tgl_buat_laporan' => 'nullable|date',
                'tgl_akhir_laporan' => 'nullable|date',
                'tahun' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]);

            $params = $request->post();


            if (!auth()->user()) {
                return [
                    'success' => false,
                    'message' => "Authentication not found"
                ];
            }
            
            
            $tgl_buat_laporan = json_encode($params['tgl_buat_laporan']);
            $tgl_akhir_laporan = json_encode($params['tgl_akhir_laporan']);
            $tahun = json_encode($params['tahun']);
            $limit = json_encode($params['limit']);
            // return $tgl_buat_laporan;

            $data = GeneralReportSource::with('investigation', 'diagnoses.disease', 'lab', 'verification.disease', 'reporter', 'species', 'specieses', 'media', 'user', 'upt', 'locations.province', 'locations.subdistrict', 'locations.district', 'locations.village')->where('status', 1);
            
            if($request->tahun != ''){
                $generalReport = $data->orderBy('report_date', 'DESC')->limit($request->limit ?? '')->whereYear('report_date', $request->tahun)->get();
            } elseif($request->tgl_buat_laporan && $request->tgl_akhir_laporan){
                $generalReport = $data->whereBetween('report_date', [json_decode($tgl_buat_laporan), json_decode($tgl_akhir_laporan)])->get();
            } 
            else{ 
                $generalReport = $data->orderBy('report_date', 'DESC')->limit($request->limit ?? '')->get();
            }
            

            $newData = [];

            foreach ($generalReport as $key => $item) {

                $result = [
                    'sehatsatli_id' => $item->id,
                    'report_date' => $item->report_date,
                    'province_name' => $item['locations']['province']['name'] ?? '',
                    'district_name' => $item['locations']['district']['name'] ?? '',
                    'subdistrict_name' => $item['locations']['subdistrict']['name'] ?? '',
                    'village_name' => $item['locations']['village']['name'] ?? '',
                    'province_code' => $item['locations']['province']['id'] ?? '',
                    'district_code' => $item['locations']['district']['id'] ?? '',
                    'subdistrict_code' => $item['locations']['subdistrict']['id'] ?? '',
                    'village_code' => $item['locations']['village']['id'] ?? '',
                    'latitude' => $item['locations']['latitude'] ?? '',
                    'longitude' => $item['locations']['longitude'] ?? '',
                    'species_name' => $item->species_name ?? $item['species']['species_name'],
                    'protected' => $item->protected ?? '',
                    'contact_number' => $item['reporter']['phone'] ?? '',
                    'contact_person' => $item['reporter']['name'] ?? '',
                    'rumor' => $item['lab']['final_diagnosis'] ?? '',
                    'verified' => $item['verification']['verified'] ?? '',
                    'verification_temporary_disease_id' => $item['verification']['temporary_disease_id'] ?? '',
                    'investigation_date' => $item['investigation']['investigation_date_from'] ?? '',
                    'lab_final_disease_id' => $item['lab']['final_disease_id'] ?? '',
                    'lab_final_diagnosis' => $item['lab']['final_diagnosis'] ?? '',
                    'investigasi_date' => $item['investigation']['investigation_date_from'] ?? '',
                    'sampling' => $item['verification']['sampling'] ?? '',
                    'action' => $item['verification']['action'] ?? '',
                    'doctor_information' => $item['verification']['doctor_information'] ?? '',
                    'diagnosis' => $item['diagnoses']['diagnosis'] ?? ''
                ];
                array_push($newData, $result);
            }


            return [
                'success' => true,
                'total' => count($newData),
                'limit' => $request->limit ?? count($newData),
                'data' => $newData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Proxy laporan dari server training eksternal
     *
     * Meneruskan request ke `sehatsatli/reportAll` pada server eksternal
     * `sehatsatlitraining.menlhk.go.id`, meneruskan header `User-key` sebagai
     * Bearer token dan `Content-type` apa adanya. Jarang dipakai mobile app.
     *
     * @header User-key string Token Bearer untuk server eksternal.
     */
    public function getApi(Request $request)
    {
        try {
            $request->validate([
                'tgl_buat_laporan' => 'nullable|date',
                'tgl_akhir_laporan' => 'nullable|date',
                'tahun' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]);

            $url = new Client(['base_uri' => 'https://sehatsatlitraining.menlhk.go.id/api/api/']);

            $token = "";

            $headers = [
                "Authorization" => 'Bearer ' . $request->header('User-key'),
                "Content-Type" => $request->header('Content-type')
            ];
            

            $response = $url->request('POST', 'sehatsatli/reportAll', [
                'body' => json_encode([
                    'tgl_buat_laporan' => $request->tgl_buat_laporan,
                    'tgl_akhir_laporan' => $request->tgl_akhir_laporan,
                    'limit' => $request->limit ?? '',
                    'tahun' => $request->tahun ?? ''
                ]),
                'headers' => $headers
            ]);
            
            

            if ($response->getReasonPhrase() != "OK") {
                return null;
            }

            $body = $response->getBody();

            $jsonString = $body->getContents();
            if ($jsonString == "") {
                return null;
            }

            $data = json_decode($jsonString, true);

            return $data;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
