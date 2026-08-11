<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendances;
use App\Models\EventReport;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    //
    private $controllerName = 'AuthController';
    /**
     * Login
     */

    /**
     * Login
     *
     * Login menggunakan email atau nomor HP sebagai username. Backend mencocokkan
     * nilai `username` terhadap kolom `username`/`username1` (masing-masing berisi
     * MD5 dari email dan nomor HP). Jika berhasil, mengembalikan Passport access
     * token (Bearer) dan data user lengkap.
     */
    public function login(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            $username = $request->username;

            // Mencari user berdasarkan username atau username1
            $user = User::where('username', $username)
                ->orWhere('username1', $username)
                ->first();
            // Jika user tidak ditemukan, beri respons yang sesuai
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Melakukan autentikasi dengan data yang ditemukan
            $data = [
                'username' => $user->username,
                'password' => $request->password,
                'status' => 1
            ];
            // Melakukan autentikasi
            if (auth()->attempt($data)) {
                // Jika autentikasi berhasil, buat akses token
                $accessToken = auth()->user()->createToken('api')->accessToken;
                $authUser = User::find(auth()->user()->id);

                Log::info($this->controllerName . '-login: success=true; username:' . $data['username']);

                $data = [
                    "user" => $authUser,
                    'access_token' => $accessToken
                ];
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                Log::info($this->controllerName . '-login: success=false; username:' . $data['username']);

                return response()->json([
                    'success' => false,
                    'message' => [
                        'username' => 'Check your username',
                        'password' => 'Check your password'
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-login: success=false; error=' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
     
     
     // public function login(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'username' => 'required',
    //             'password' => 'required',
    //             'token' => 'required'
    //         ]);
    //         $token = $request->token;
    //         $secret = env('NOCAPTCHA_SECRET');

    //         $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
    //             'secret' => $secret,
    //             'response' => $token,
    //         ]);

    //         $continue = false;
    //         $responseData = $response->json();

    //         if ($responseData['success']) {
    //             $continue = true;
    //         }

    //         if (!$continue) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Recaptcha failed'
    //             ], 200);
    //         }

    //         $username = $request->username;

    //         //$user = User::where('phone', $phone)->first();
    //         $user = User::where('username', $username)
    //             ->orWhere('username1', $username)
    //             ->first();

    //         $data = [
    //             'username' => $user->username,
    //             'password' => $request->password,
    //             'status' => 1
    //         ];

    //         //dd($data);

    //         if (auth()->attempt($data)) {
    //             $accessToken = auth()->user()->createToken('api')->accessToken;
    //             $authUser = User::find(auth()->user()->id);

    //             Log::info($this->controllerName . '-login: success=true; username:' . $data['username']);
    //             $data = [
    //                 "user" => $authUser,
    //                 'access_token' => $accessToken
    //             ];
    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $data
    //             ]);
    //         } else {

    //             Log::info($this->controllerName . '-login: success=false; username:' . $data['username']);

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => [
    //                     'username' => 'Check your username',
    //                     'password' => 'Check your password'
    //                 ]
    //             ], 200);
    //         }
    //     } catch (\Exception $e) {
    //         Log::info($this->controllerName . '-login: success=false; error=' . $e->getMessage());
    //         // activity()->log($this->controllerName . '-login: success=false; error=' . $e->getMessage());
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    /**
     * Login (mobile app)
     *
     * Login standar menggunakan `email` (bukan `username`/MD5). Dipakai khusus
     * oleh mobile app.
     */
    public function loginApp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $email = $request->email;

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $data = [
                'email' => $user->email,
                'password' => $request->password,
                'status' => 1
            ];

            if (auth()->attempt($data)) {
                $accessToken = auth()->user()->createToken('api')->accessToken;
                $authUser = User::find(auth()->user()->id);

                Log::info($this->controllerName . '-loginApp: success=true; email:' . $data['email']);
                $data = [
                    "user" => $authUser,
                    'role' => $authUser->user_level,
                    'access_token' => $accessToken
                ];
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            } else {

                Log::info($this->controllerName . '-loginApp: success=false; email:' . $data['email']);

                return response()->json([
                    'success' => false,
                    'message' => [
                        'email' => 'Check your email',
                        'password' => 'Check your password'
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-loginApp: success=false; error=' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Register
     *
     * Registrasi akun baru hanya dengan `email` dan `password`. `username`
     * di-generate otomatis dari MD5(email), mengikuti pola yang dipakai
     * `login()`/`UserController::save()`. Berhasil register langsung
     * mengembalikan Passport access token (Bearer), sama seperti `/auth/login`.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]);

            $email = $request->email;

            $user = new User();
            $user->name = explode('@', $email)[0];
            $user->email = $email;
            $user->username = md5($email);
            $user->password = Hash::make($request->password);
            $user->reset_password = false;
            $user->save();

            $accessToken = $user->createToken('api')->accessToken;

            Log::info($this->controllerName . '-register: success=true; email=' . $email);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'access_token' => $accessToken,
                ]
            ]);
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-register: success=false; error=' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout
     *
     * Mencabut (revoke) Passport access token yang sedang dipakai dan menghapus
     * FCM device token (`devices`) milik user yang login, sehingga device berhenti
     * menerima push notification.
     */
    public function logout(Request $request)
    {
        try {
            $authUser = auth()->user();

            $user = User::where('status', 1)->where('id', $authUser->id)->update([
                'devices' => null
            ]);

            $token = $authUser->token();
            $token->revoke();

            $response = ['message' => 'You have successfully logged out!'];

            Log::info($this->controllerName . '-logout: success=true; user=' . $authUser->username);
            // activity()->log($this->controllerName . '-logout: success=true; user=' . $authUser->username);

            // return response($response, 200)->withCookie($cookie);
            return response($response, 200);
        } catch (\Exception $e) {
            Log::info($this->controllerName . '-logout: success=false; error=' . $e->getMessage());
            activity()->log($this->controllerName . '-logout: success=false; error=' . $e->getMessage());


            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ganti password
     *
     * Mengganti password user yang sedang login (bukan alur reset password lewat
     * email/OTP). Umumnya dipanggil saat `user.reset_password == true`, yaitu ketika
     * admin membuat akun baru dan memaksa user mengganti password default saat
     * login pertama.
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);

            $auth = auth()->user();
            $params = $request->only([
                'password'
            ]);
            $user = User::where('id', $auth->id)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Error load data'
                ];
            }
            $user->password = Hash::make($params['password']);
            $user->reset_password = 0;
            $user->save();

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
