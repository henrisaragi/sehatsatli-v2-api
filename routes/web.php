<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return "Database terkoneksi: " . DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        return "Tidak bisa terkoneksi ke database: " . $e->getMessage();
    }
});


Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

// Route::get('users', [User::class, 'show']);
Route::get('ada', function(){
    return 'ada';
});
