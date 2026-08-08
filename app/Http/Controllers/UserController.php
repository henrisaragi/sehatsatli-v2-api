<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserController extends Controller
{
    //
    private $controllerName = 'UserController';
    /**
     * Login
     */
    /**
     * List user
     *
     * Mengembalikan user lain yang levelnya (`user_level`) lebih rendah/junior
     * dari user yang login (hierarki: angka lebih kecil = lebih senior).
     * Otomatis di-scope ke UPT user login jika `upt_id > 2`.
     */
    public function getAll(Request $request)
    {
        try {
            $current_user = User::find(auth()->user()->id);
            $query = User::where('id', "!=", $current_user->id)
                ->where('status', 1)
                ->where('user_level', ">", $current_user->user_level);

            if ($current_user->upt_id) {
                if ($current_user->upt_id > 2) {
                    $query->where('upt_id', $current_user->upt_id);
                }
            }
            $user = $query->get();

            return [
                'success' => true,
                'data' => $user
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
     * Detail 1 user
     */
    public function getOne(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();

            $user = User::with('media', 'upt')
                ->where('status', 1)
                ->where('id', $params['id'])
                ->first();

            return [
                'success' => true,
                'data' => $user
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
     * Buat/ubah user
     *
     * Kirim `id` untuk mengubah user yang sudah ada. Saat membuat user baru,
     * `password` dan `password_confirm` wajib diisi dan harus sama persis.
     * `username`/`username1` dibuat otomatis oleh server (MD5 dari `email` dan
     * `phone`) — jangan dikirim manual, dan pastikan `email`/`phone` unik &
     * konsisten karena keduanya dipakai sebagai kredensial login.
     */
    public function save(Request $request)
    {
        $currentUser = auth()->user();

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'phones' => 'nullable|array',
                'gender' => 'nullable|string',
                'occupation' => 'nullable|string',
                'user_level' => 'nullable|integer',
                'upt_id' => 'nullable|integer',
                'upt_type' => 'nullable|string',
                'trained' => 'nullable|boolean',
                'is_doctor' => 'nullable|boolean',
                'show_in_contact' => 'nullable|boolean',
                'training_mode' => 'nullable|boolean',
                'web_admin' => 'nullable|boolean',
                'all_notification' => 'nullable|boolean',
                'reset_password' => 'nullable|boolean',
                'drh_specialist' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'password' => 'nullable|string',
                'password_confirm' => 'nullable|string',
                'heads_upt' => 'nullable|boolean',
            ]);

            $params = $request->only([
                'id',
                //'username',
                'name',
                'email',
                'phone',
                'phones',
                'gender',
                'occupation',
                'user_level',
                'upt_id',
                'upt_type',
                'trained',
                'is_doctor',
                'show_in_contact',
                'training_mode',
                'web_admin',
                'all_notification',
                'reset_password',
                'drh_specialist',
                'is_active',
                'password',
                'password_confirm',
                'heads_upt'
            ]);

            // TODO: Aldi
            // Cek user skrg groupnya = 4 ga? kl iya, ga bole saving
            // Kalo gruup 3 hanya bisa update / create di lokasinya (UPT id yg sama) aja 
            // sisanya bole create dimana aja ()

            $user = null;
            if (array_key_exists('id', $params) && $params['id']) {
                $user = User::where('status', 1)->where('id', $params['id'])->first();
                if (!$user) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }
                if (array_key_exists('password', $params)) {
                    if (
                        $params['password'] &&
                        $params['password_confirm'] &&

                        // array_key_exists('password', $request->post) &&
                        // array_key_exists('password_confirm', $request->post) &&
                        $params['password'] == $params['password_confirm']
                    ) {
                        $user->password = Hash::make($params['password']);
                    }
                }


                // mass assignment
                $user->fill($params);
                // encrypt with md5 for username and password to username and username1
                $user->username = md5($params['email']);
                $user->username1 = md5($params['phone']);
                $user->save($params);
            } else {
                $user = new User($params);
                // $this->setToken($request);
                if (
                    array_key_exists('password', $params) &&
                    array_key_exists('password_confirm', $params) &&
                    $params['password'] == $params['password_confirm']
                ) {
                    $user->password = Hash::make($params['password']);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Password does not match'
                    ];
                }
                // encrypt with md5 for username and password to username and username1
                $user->username = md5($params['email']);
                $user->username1 = md5($params['phone']);
                $user->save();
            }

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot save data",
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update profil sendiri
     *
     * ⚠️ Catatan implementasi: kode saat ini mengecek `array_key_exists('id', $params)`
     * padahal `id` tidak pernah diminta lewat `$request->only([...])` di atas —
     * akibatnya cabang update **tidak pernah tereksekusi** dan endpoint ini selalu
     * mengembalikan `{success:true, data:null}` tanpa benar-benar menyimpan
     * perubahan. Perlu diperbaiki di sisi backend sebelum diandalkan mobile app.
     */
    public function updateProfile(Request $request)
    {
        $currentUser = auth()->user();
        try {
            $request->validate([
                'name' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'phones' => 'nullable|array',
                'gender' => 'nullable|string',
                'occupation' => 'nullable|string',
                'user_level' => 'nullable|integer',
                'upt_id' => 'nullable|integer',
                'upt_type' => 'nullable|string',
                'trained' => 'nullable|boolean',
                'is_doctor' => 'nullable|boolean',
                'show_in_contact' => 'nullable|boolean',
                'training_mode' => 'nullable|boolean',
                'web_admin' => 'nullable|boolean',
                'all_notification' => 'nullable|boolean',
                'reset_password' => 'nullable|boolean',
                'drh_specialist' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'password' => 'nullable|string',
                'password_confirm' => 'nullable|string',
                'heads_upt' => 'nullable|boolean',
            ]);

            $params = $request->only([
                //'id',
                'name',
                'email',
                'phone',
                'phones',
                'gender',
                'occupation',
                'user_level',
                'upt_id',
                'upt_type',
                'trained',
                'is_doctor',
                'show_in_contact',
                'training_mode',
                'web_admin',
                'all_notification',
                'reset_password',
                'drh_specialist',
                'is_active',
                'password_confirm',
                'password',
                'heads_upt'
            ]);

            // TODO: Aldi,
            // Cek yang ini currentUser-nya apakah sama dengan ID-nya

            $user = null;
            if (array_key_exists('id', $params)) {
                $user = User::where('status', 1)->where('id', $currentUser->id)->first();
                if (!$user) {
                    return [
                        'success' => false,
                        'message' => "Error, data not found"
                    ];
                }
                if (
                    $params['password'] &&
                    $params['password_confirm'] &&
                    // array_key_exists('password', $request->post) &&
                    // array_key_exists('password_confirm', $request->post) &&
                    $params['password'] == $params['password_confirm']
                ) {
                    $user->password = Hash::make($params['password']);
                }
                // mass assignment
                $user->save();

                // if (array_key_exists('password', $params) && array_key_exists('password_confirm', $params) && $params['password'] == $params['password_confirm']) {
                // }
            } else {
                // Ga bole ubah
                return [
                    'success' => true,
                    'data' => null
                ];
            }

            return [
                'success' => true,
                'data' => $user
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
     * Upload foto profil (web)
     *
     * Menghapus semua foto lama lalu menyimpan file baru dari `file`
     * (multipart/form-data) sebagai foto tunggal user.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'required|file',
            ]);

            $id = $request->input('id');

            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            foreach ($user->media as $id => $media) {
                $media->delete();
            }

            $user->addMediaFromRequest('file')
                ->toMediaCollection();

            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            Log::error($this->controllerName . '-uploadFile: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data",
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hapus user
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
            $user = null;

            if (!array_key_exists('id', $params)) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $user = User::where('status', 1)->where('id', $params['id'])->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => "Error, data not found"
                ];
            }

            $user->status = 0;
            $user->save();

            return [
                'success' => true,
                'data' => $user
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
     * Simpan FCM device token
     *
     * Menyimpan/menimpa token push notification (`devices`) milik user yang
     * login. Panggil setiap kali app mendapat/memperbarui token FCM dari device.
     */
    public function setToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'nullable|string',
            ]);

            $user = auth()->user();

            $token = $request->input('token');
            $user = User::where('status', 1)->where('id', $user->id)->update([
                'devices' => $token
            ]);

            return [
                'success' => true,
                'message' => 'Success. '
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-notif: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * List dokter hewan
     *
     * Endpoint publik — daftar user dengan `is_doctor = 1`. Dipakai untuk
     * memilih dokter saat mengisi tahap verifikasi laporan.
     */
    public function getAllDokter()
    {
        try {
            $dokter = User::where('status', 1)->where('is_doctor', 1)->get();

            return response()->json([
                'success' => true,
                'data' => $dokter
            ]);
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-notif: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error. '
            ];
        }
    }

    /**
     * Detail 1 dokter hewan
     *
     * Endpoint publik. ⚠️ Filter `is_doctor = 0` tampak terbalik dibanding
     * `getAllDokter` (`is_doctor = 1`) — kemungkinan bug, hasil bisa kosong
     * untuk id dokter yang valid.
     */
    public function getOneDokter(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $params = $request->post();
            $dokter = User::where('id', $params['id'])->where('status', 1)->where('is_doctor', 0)->first();
            return [
                'success' => true,
                'data' => $dokter
            ];
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-notif: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error. '
            ];
        }
    }

    /**
     * Upload foto profil (mobile)
     *
     * Versi mobile dari upload foto profil — file dikirim base64 di `file`
     * (bukan multipart) dan menggantikan foto lama.
     */
    public function uploadFileMobile(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'file' => 'nullable|string',
            ]);

            $id = $request->input('id');
            $file = $request->input('file');

            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            foreach ($user->media as $id => $media) {
                $media->delete();
            }

            // $user->addMediaFromRequest('file')
            //     ->toMediaCollection();
            // $user->addMediaFromBase64($post['file'])
            //     ->usingFileName('foto.png')
            //     ->toMediaCollection();
            $image = $request->input(
                'file'
            );
            if ($image) {
                // $images = array_values([$image]);

                // foreach ($images as $i1) {
                Log::info($image);
                if (is_string($image)) {
                    $user->addMediaFromBase64($image)->usingFileName('foto.png')->toMediaCollection('media');
                }
            }

            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();


            return [
                'success' => true,
                // 'data' => $user
            ];
        } catch (Exception $e) {
            Log::error($this->controllerName . '-uploadFile: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data",
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim notifikasi ke 1 user
     *
     * Mengirim push notification (FCM) langsung ke device (`devices`) milik
     * user tertentu, dengan judul/isi bebas.
     */
    public function sendNotificationUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $params = $request->post();
        $SERVER_API_KEY = env('APP_SERVER_API_KEY');


        $firebaseToken = User::where('status', 1)->where('id', $params['user_id'])->first();

        $subject = $params['title'];
        $body = $params['description'];

        // foreach ($firebasePETUGAS as $key => $item) {
        //     $userInbox = new UserInbox();
        //     // $userInbox->creator = auth()->user()->id;
        //     // $userInbox->updater = auth()->user()->id;
        //     $userInbox->status = 0;
        //     $userInbox->user_id = $item->id;
        //     $userInbox->received_date = date('Y-m-d');
        //     $userInbox->read_date = date('Y-m-d');
        //     $userInbox->subject =  $subject;
        //     $userInbox->message = $body;
        //     // $userInbox->read = ;
        //     $userInbox->save();
        // }

        $data = array(
            // 'registration_ids' => $firebaseToken->devices,
            'to' => $firebaseToken->devices,
            'notification' => [
                'title' => $subject,
                'body' => $body,
            ],
        );

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
}
