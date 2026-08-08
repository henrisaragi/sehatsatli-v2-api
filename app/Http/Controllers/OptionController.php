<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class OptionController extends Controller
{
    //
    private $controllerName = 'OptionController';
    /**
     * Login
     */
    public function getAll(Request $request)
    {
        try {
            $option = Option::get();

            return response()->json([
                'success' => true,
                'data' => $option
            ]);
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

            $option = Option::where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $option
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
            $option = null;
            if (array_key_exists('id', $params)) {
                $option = Option::where('status', 1)->where('id', $params['id'])->first();
                if (!$option) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                $params['updater'] = $user->id;
                $option->save($params);
            } else {
                $params['creator'] = $user->id;
                $option = Option::create($params);
            }

            return [
                'success' => true,
                'data' => $option
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
