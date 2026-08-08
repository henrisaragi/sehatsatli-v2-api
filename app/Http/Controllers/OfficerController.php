<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class OfficerController extends Controller
{
    private $controllerName = 'OfficerController';

    public function getAll(Request $request){
        try {
            $officer = Officer::with(['upt'])->get();

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
    
}
