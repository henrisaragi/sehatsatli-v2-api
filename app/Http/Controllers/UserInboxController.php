<?php

namespace App\Http\Controllers;

use App\Models\UserInbox;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class UserInboxController extends Controller
{
    //
    private $controllerName = 'UserInboxController';
    /**
     * Login
     */
    /**
     * List pesan inbox
     *
     * Seluruh pesan/notifikasi in-app (`UserInbox`) yang aktif.
     */
    public function getAll(Request $request)
    {
        try {
            $userinbox = UserInbox::where('status', 1)->get();
            
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
     * Detail 1 pesan inbox
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();

            $userinbox = UserInbox::where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $userinbox
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
     * Buat/ubah pesan inbox
     *
     * Kirim `id` untuk mengubah pesan yang sudah ada.
     */
    public function save(Request $request)
    {
        $user = auth()->user();
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'status' => 'nullable|integer',
                'user_id' => 'nullable|integer',
                'received_date' => 'nullable|date',
                'read_date' => 'nullable|date',
                'subject' => 'nullable|string',
                'message' => 'nullable|string',
                'read' => 'nullable|boolean',
            ]);

            $params = $request->post();
            $userinbox = null;
            if (array_key_exists('id', $params)) {
                $userinbox = UserInbox::where('status', 1)->where('id', $params['id'])->first();
                if (!$userinbox) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }

                // mass assignment
                $params['updater'] = $user->id;
                $userinbox->save($params);
            } else {
                $params['creator'] = $user->id;
                $userinbox = UserInbox::create($params);
            }

            return [
                'success' => true,
                'data' => $userinbox
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data"
            ];
        }
    }

    /**
     * Hapus pesan inbox
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
            $userinbox = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $userinbox = UserInbox::where('status', 1)->where('id', $params['id'])->first();
            if (!$userinbox) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $userinbox->status = 0;
            $userinbox->save();

            return [
                'success' => true,
                'data' => $userinbox
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
