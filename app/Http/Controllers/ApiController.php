<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Disease;
use App\Models\District;
use App\Models\UPT;
use App\Models\User;
use App\Models\UserInbox;
use App\Models\Species;
use App\Models\Option;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Village;

use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

//use Auth;

class ApiController extends Controller
{
    private $repo;

    public function __construct()
    {
        //$this->middleware('auth');
    }


    /**
     * Master data (Option)
     *
     * Mengembalikan seluruh baris tabel `options` sebagai map `name => value`
     * (dropdown/lookup untuk form: upt_type, sample_results, case_status,
     * species_category, suspek_disease, protected, dsb), ditambah flag
     * `trained` (nilai `training_mode` user yang login).
     */
    public function getMaster(Request $request)
    {
        $options = Option::pluck('value', 'name');
            // Mendapatkan user yang sedang login
      
        // Memastikan user tidak null sebelum mengambil status training_mode
        $trained = auth()->user()->training_mode;

        return response()->json([
            'success' => true,
            'data' => [
                'options' => $options,
                'trained' => $trained
            ]
        ]);
    }

    public function groupBy($collection)
    {
        $result = [];
        foreach ($collection as $item) {
            $group = [];
            if (array_key_exists($item->g, $result)) {
                $group = $result[$item->g];
            }
            // array_push($group, [
            //     'id' => $item->id,
            //     'name' => $item->name,
            // ]);
            if ($item->name && $item->name != "") {
                $group[$item->id] = Str::slug($item->name);
                $result[$item->g] = $group;
            }
        }
        return $result;
    }

    // public function getLocations1(Request $request)
    // {
    // $userAuth = auth()->user();
    // $user = User::with('upt')->find($userAuth->id);
    // $provinces = Province::pluck('name', 'id');
    // $provinces = Redis::get('api:provinces');
    // if (!$provinces) {
    //     $provinces = Province::get(['id', 'name']);
    //     Redis::set("api:provinces", json_encode($provinces));
    // } else {
    //     $provinces = json_decode($provinces);
    // }

    // $districts = $this->groupBy(District::get(['id', 'id_province as g', 'name']));
    // $districts = Redis::get('api:districts');
    // if (!$districts) {
    //     $districts = $this->groupBy(District::get(['id', 'id_province as g', 'name']));
    //     Redis::set("api:districts", json_encode($districts));
    // } else {
    //     $districts = json_decode($districts);
    // }


    // $subdistricts = $this->groupBy(SubDistrict::get([
    //     'id', 'id_district as g', 'name'
    // ]));

    // $subdistricts = Redis::get('api:subdistricts');
    // if (!$subdistricts) {
    //     $subdistricts = $this->groupBy(SubDistrict::get(['id', 'id_district as g', 'name']));
    //     Redis::set("api:subdistricts", json_encode($subdistricts));
    // } else {
    //     $subdistricts = json_decode($subdistricts);
    // }

    // $villages = Redis::get('api:villages');
    // if (!$villages) {
    //     $villages = Village::get(['id', 'id_sub_district', 'name'])->groupBy('id_sub_district');
    //     Redis::set("api:villages", json_encode($villages));
    // } else {
    //     $villages = json_decode($villages);
    // }
    // $villages = $this->groupBy(Village::get(['id', 'id_sub_district as g', 'name']));
    // $villages = Village::limit(2000)->get(['id', 'id_sub_district', 'name'])->groupBy('id_sub_district');
    // return response()->json([
    //     'success' => true, "data" => [
    //         'provinces' => $provinces,
    //         'districts' => $districts,
    //         'subdistricts' => $subdistricts,
    //         'villages' => $villages,
    //     ],
    // ]);
    // }

    /**
     * Wilayah administratif (semua)
     *
     * Mengembalikan seluruh provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan
     * se-Indonesia (kabupaten/kecamatan/desa dikelompokkan per induknya). Hasil
     * di-cache di Redis (`api:provinces`, `api:districts`, `api:subdistricts`,
     * `api:villages`).
     */
    public function getLocations(Request $request)
    {
        try {
            $provinces = Redis::get('api:provinces');
            if (!$provinces) {
                $provinces = Province::get(['id', 'name']);
                Redis::set("api:provinces", json_encode($provinces));
            } else {
                $provinces = json_decode($provinces);
            }


            $districts = Redis::get('api:districts');
            if (!$districts) {
                $districts = District::get(['id', 'id_province', 'name'])->groupBy('id_province');
                Redis::set("api:districts", json_encode($districts));
            } else {
                $districts = json_decode($districts);
            }


            $subdistricts = Redis::get('api:subdistricts');
            if (!$subdistricts) {
                $subdistricts = SubDistrict::get(['id', 'id_district', 'name'])->groupBy('id_district');
                Redis::set("api:subdistricts", json_encode($subdistricts));
            } else {
                $subdistricts = json_decode($subdistricts);
            }

            $villages = Redis::get('api:villages');
            if (!$villages) {
                $villages = Village::get(['id', 'id_sub_district', 'name'])->groupBy('id_sub_district');
                Redis::set("api:villages", json_encode($villages));
            } else {
                $villages = json_decode($villages);
            }


            // $villages = Redis::get('api:villages');
            // if (!$villages) {
            //     $villages = Village::get(['id', 'id_sub_district', 'name'])->take(10000)->groupBy('id_sub_district');
            //     // Redis::set("api:villages", json_encode($villages));
            // } else {
            //     $villages = json_decode($villages);
            // }

            // $villages = Village::get(['id', 'id_sub_district', 'name'])->groupBy('id_sub_district');

            return response()->json([
                'success' => true, "data" => [
                    'provinces' => $provinces,
                    'districts' => $districts,
                    'subdistricts' => $subdistricts,
                    'villages' => $villages
                ]
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    // public function getProvince(Request $request)
    // {
    //     //
    //     try {
    //         //code...
    //         $province = DB::table('ms_province')->select('*')->get();
    //         // select('id', 'name', 'latin_name', 'animal_type')->get();

    //         return response()->json($province, 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         Log::info($th);
    //         $data = ["error"];
    //         return response()->json($th, 500);
    //     }
    // }


    // public function getDistrict(Request $request)
    // {
    //     //
    //     try {
    //         //code...

    //         $bodyContent = $request->post();
    //         $district = DB::table('ms_district')->where('id_province', $bodyContent['id'])->get();
    //         $district->values("id", "name", "code")->all();
    //         return response()->json($district, 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         Log::info($th);
    //         $data = ["error"];
    //         return response()->json($data, 500);
    //     }
    // }
    // public function getSubDistrict(Request $request)
    // {
    //     //
    //     try {
    //         //code...
    //         $bodyContent = $request->post();
    //         $sub_district = DB::table('ms_sub_district')->where('id_district', $bodyContent['id'])->get();
    //         $sub_district->values("id", "name", "code")->all();
    //         return response()->json($sub_district, 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         Log::info($th);
    //         $data = ["error"];
    //         return response()->json($th, 500);
    //     }
    // }
    // public function getVillage(Request $request)
    // {
    //     //
    //     try {
    //         //code...
    //         $bodyContent = $request->post();
    //         $village = DB::table('ms_village')->where('id_sub_district', $bodyContent['id'])->get();
    //         $village->values("id", "name", "code")->all();
    //         return response()->json($village, 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         Log::info($th);
    //         $data = ["error"];
    //         return response()->json($data, 500);
    //     }
    // }


    public function refreshSession(Request $request)
    {
    }

    /**
     * Wilayah administratif user login
     *
     * Sama seperti `/locations`, tapi hanya berisi provinsi/kab-kota/kecamatan/desa
     * yang berada di bawah provinsi tempat UPT user yang login berada (di-scope
     * lewat `user.upt_type` → `UPT.province_id`).
     */
    public function LocationUser(Request $request)
    {

        $user = auth()->user();
        // $userUpt = $user->upt_type;

        $upt = UPT::where('type', $user->upt_type)->first();
        $provinces = Province::where('id', $upt->province_id)->get();
        $districts = '';
        $subdistricts = '';
        $villages = '';
        foreach ($provinces as $province) {
            $districts = District::where('id_province', $province->id)->get();
            $subdistricts = SubDistrict::where('id_province', $province->id)->get();
            $villages = Village::where('id_province', $province->id)->get();
        }
        return response()->json([
            'success' => true, "data" => [
                'provinces' => $provinces,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'villages' => $villages,
            ],
        ]);
    }

    public function provinces()
    {
        $provinces = Redis::get('api:provinces');
        if (!$provinces) {
            $provinces = Province::get('id', 'name');
            Redis::set('api:provinces', json_encode($provinces));
        } else {
            $provinces = json_decode($provinces);
        }

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    public function districts(Request $request)
    {
        $params = $request->post();
        $districts = District::where('id_province', $params['id_province'])->get(['id', 'name', 'id_province']);
        // $districts = Redis::get('api:districts');
        // if (!$districts) {
        //     $districts = District::where('id_province', $params['id_province'])->get(['id', 'name', 'id_province']);
        //     Redis::set('api:districts', json_decode($districts));
        // } else {
        //     $districts = json_decode($districts);
        // }

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    public function subdistricts(Request $request)
    {
        $params = $request->post();
        $subdistricts = SubDistrict::where('id_district', $params['id_district'])->get(['id', 'name', 'id_district']);
        // $subdistricts = Redis::get('api:subdistricts');
        // if (!$subdistricts) {
        //     $subdistricts = SubDistrict::where('id_district', $params['id_district'])->get(['id', 'name', 'id_district']);
        //     Redis::set('api:subdistricts', json_decode($subdistricts));
        // } else {
        //     $subdistricts = json_decode($subdistricts);
        // }

        return response()->json([
            'success' => true,
            'data' => $subdistricts
        ]);
    }

    public function villages(Request $request)
    {
        // $params = $request->post();
        // $villages = Village::where('id_sub_district', $params['id_sub_district'])->get(['id', 'id_sub_district', 'name']);
        // $villages = Village::get(['id', 'id_sub_district', 'name']);
        $villages = Redis::get('api:villages');
        if (!$villages) {
            // $villages = Village::where('id_sub_district', $params['id_sub_district'])->get(['id', 'id_sub_district', 'name']);
            $villages = Village::get(['id', 'id_sub_district', 'name'])->groupBy('id_sub_district');
            Redis::set("api:villages", json_encode($villages));
        } else {
            $villages = json_decode($villages);
        }

        return response()->json([
            'success' => true, "data" => [
                'villages' => $villages,
            ],
        ]);
    }
}
