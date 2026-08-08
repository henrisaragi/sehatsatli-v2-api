<?php

namespace App\Http\Controllers;

use App\Models\UptSpecies;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class UptSpeciesController extends Controller
{
    //
    private $controllerName = 'UptSpeciesController';
    /**
     * Login
     */
    public function getAll(Request $request)
    {
        try {
            $uptspecies = UptSpecies::where('status', 1)->get();
            
            return [
                'success' => true,
                'data' => $uptspecies
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getAll: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function getOne(Request $request)
    {
        try {
            $params = $request->post();

            $uptspecies = UptSpecies::where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $uptspecies
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }

    public function save(Request $request)
    {
        $user = auth()->user();
        try {
            $params = $request->post();
            $uptspecies = null;
            if (array_key_exists('id', $params)) {
                $uptspecies = UptSpecies::where('status', 1)->where('id', $params['id'])->first();
                if (!$uptspecies) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                $params['updater'] = $user->id;
                $uptspecies->save($params);
            } else {
                $params['creator'] = $user->id;
                $uptspecies = UptSpecies::create($params);
            }

            return [
                'success' => true,
                'data' => $uptspecies
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
        }
    }

    public function delete(Request $request)
    {
        try {
            $params = $request->post();
            $uptspecies = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $uptspecies = UptSpecies::where('status', 1)->where('id', $params['id'])->first();
            if (!$uptspecies) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $uptspecies->status = 0;
            $uptspecies->save();

            return [
                'success' => true,
                'data' => $uptspecies
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-delete: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot delete data"
            ];
        }
    }
}
