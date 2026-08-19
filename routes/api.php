<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiGeneralReportController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\GeneralReportSourceController;

use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\UPTController;
use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OutputLaporanController;

use App\Http\Controllers\GeneralReportAcknowledgementController;
use App\Http\Controllers\GeneralReportDiagnosisController;
use App\Http\Controllers\GeneralReportLabController;
use App\Http\Controllers\GeneralReportLocationController;
use App\Http\Controllers\GeneralReportReporterController;
use App\Http\Controllers\GeneralReportSpeciesController;
use App\Http\Controllers\GeneralReportVerificationController;
use App\Http\Controllers\UptSpeciesController;
use App\Http\Controllers\UserInboxController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\CaseDiseaseController;
use App\Http\Controllers\CommunityReportController;
use App\Http\Controllers\GeneralReportSourceMobileController;
use Illuminate\Support\Facades\Route;

// Route::post('generalreportdiagnosis/getAll', [GeneralReportDiagnosisController::class, 'getAll']);
// Route::post('generalreportdiagnosis/getOne', [GeneralReportDiagnosisController::class, 'getOne']);
// Route::post('generalreportdiagnosis/save', [GeneralReportDiagnosisController::class, 'save']);



// Route::post('generalreportlab/getAll', [GeneralReportLabController::class, 'getAll']);
// Route::post('generalreportlab/getOne', [GeneralReportLabController::class, 'getOne']);
// Route::post('generalreportlab/save', [GeneralReportLabController::class, 'save']);
// Route::post('generalreportlab/delete', [GeneralReportLabController::class, 'delete']);

// Route::post('officer/getAll', [OfficerController::class, 'getAll']);

// Route::post('casedisease/getAll', [CaseDiseaseController::class, 'getSuspecDisease']);
// Route::post('casenondisease/getAll', [CaseDiseaseController::class, 'getNonDisease']);

$apiRoutes = function () {
    Route::post('dashboard/get-Suspek', [OutputLaporanController::class, 'getSuspek']);
    Route::post('dashboard/get-NonSuspek', [OutputLaporanController::class, 'getNonSuspek']);
    Route::post('dashboard/get-SuspekNotFound', [OutputLaporanController::class, 'getSuspekNotFound']);

    Route::post('general-reports/getOne', [GeneralReportSourceController::class, 'getOne']);

    Route::post('dashboard/get-lab', [OutputLaporanController::class, 'lab']);
    Route::post('dashboard/get-suspekPenyakit', [OutputLaporanController::class, 'suspekPenyakit']);
    Route::post('dashboard/get-nonPenyakit', [OutputLaporanController::class, 'nonPenyakit']);
    Route::post('dashboard/get-petugas', [OutputLaporanController::class, 'petugas']);
    Route::post('dashboard/get-upt', [OutputLaporanController::class, 'UPT']);
    Route::post('dashboard/get-rincian', [OutputLaporanController::class, 'rincianLaporan']);
    Route::post('dashboard/get-totalLaporan', [DashboardController::class, 'report_perbulan']);
    Route::post('dashboard/get-location', [DashboardController::class, 'dashboard']);

    Route::post('upts/getAll', [UPTController::class, 'getAll']);
    Route::post('upts/getOne', [UPTController::class, 'getOne']);
    Route::post('upts/save', [UPTController::class, 'save']);
    Route::post('upts/delete', [UPTController::class, 'delete']);
    Route::post('upts/uploadPhoto', [UPTController::class, 'uploadFile']);

    Route::post('diseases/getAll', [DiseaseController::class, 'getAll']);
    Route::post('diseases/getOne', [DiseaseController::class, 'getOne']);
    Route::post('diseases/save', [DiseaseController::class, 'save']);
    Route::post('diseases/delete', [DiseaseController::class, 'delete']);
    Route::post('diseases/uploadPhoto', [DiseaseController::class, 'uploadFile']);

    Route::post('species/getAll', [SpeciesController::class, 'getAll']);
    Route::post('species/getOne', [SpeciesController::class, 'getOne']);
    Route::post('species/save', [SpeciesController::class, 'save']);
    Route::post('species/delete', [SpeciesController::class, 'delete']);
    Route::post('species/uploadPhoto', [SpeciesController::class, 'uploadFile']);

    Route::post('users/getAll', [UserController::class, 'getAll']);
    Route::post('users/getOne', [UserController::class, 'getOne']);
    Route::post('users/save', [UserController::class, 'save']);
    Route::post('users/delete', [UserController::class, 'delete']);
    Route::post('users/uploadPhoto', [UserController::class, 'uploadFile']);
    Route::post('update-user', [UserController::class, 'updateProfile']);

    Route::post('users/uploadPhotoMobile', [UserController::class, 'uploadFileMobile']);

    Route::post('general-reports/getAll', [GeneralReportSourceController::class, 'getAll']);
    Route::post('general-reports/getOne', [GeneralReportSourceController::class, 'getOne']);
    Route::post('general-reports/getLastId', [GeneralReportSourceController::class, 'getLastId']);
    Route::post('general-reports/delete', [GeneralReportSourceController::class, 'delete']);
    Route::post('general-reports/save', [GeneralReportSourceController::class, 'save']);
    Route::post('general-reports/uploadPhoto', [GeneralReportSourceController::class, 'uploadFile']);
    Route::post('general-reports/saveVerify', [GeneralReportSourceController::class, 'saveVerify']);
    Route::post('general-reports/saveLab', [GeneralReportSourceController::class, 'saveLab']);
    Route::post('general-reports/saveInvestigation', [GeneralReportSourceController::class, 'saveInvestigation']);

    Route::post('user-activities/getAll', [UserActivityController::class, 'getAll']);
    Route::post('user-activities/track', [UserActivityController::class, 'save']);
    Route::post('user-active', [DashboardController::class, 'userActive']);

    Route::post('masters', [ApiController::class, 'getMaster']);
    Route::post('locations', [ApiController::class, 'getLocations']);
    Route::post('location-user', [ApiController::class, 'LocationUser']);
    Route::post('general-reports/saveGeneralReportMobile', [GeneralReportSourceMobileController::class, 'save']);;
    Route::post('general-reports/deleteGeneralReportMobile', [GeneralReportSourceMobileController::class, 'delete']);;
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::post('users/user/token', [UserController::class, 'setToken']);
    Route::post('users/sendNotification', [UserController::class, 'sendNotificationUser']);
    Route::post('send/notification/satwa-protected', [GeneralReportSourceController::class, 'sendNotifikasiSatwaDiLindungi']);
    Route::post('send/notification/disease-priority', [GeneralReportSourceController::class, 'sendNotificationDiseasePriority']);
    Route::post('general-reports/getGeneralReportMobile', [GeneralReportSourceMobileController::class, 'getAll']);;

    Route::post('user-inbox/getAll', [UserInboxController::class, 'getAll']);
    Route::post('user-inbox/getOne', [UserInboxController::class, 'getOne']);
    Route::post('user-inbox/save', [UserInboxController::class, 'save']);
    Route::post('user-inbox/delete', [UserInboxController::class, 'delete']);

    Route::post('community-reports/getOne', [CommunityReportController::class, 'getOne']);
    Route::post('sehatsatli/reportAll', [ApiGeneralReportController::class, 'index']);

    Route::post('send/notificationreports', [GeneralReportSourceMobileController::class, 'sendNotificationReport']);

    Route::post('masterss', [ApiController::class, 'getMaster']);
};
Route::post('community-reports/getAll', [CommunityReportController::class, 'getAll']);
Route::post('community-reports/delete', [CommunityReportController::class, 'delete']);
Route::group(['middleware' => ['auth:api']], $apiRoutes);
Route::post('dokter/getAll', [UserController::class, 'getAllDokter']);
Route::post('dokter/getOne', [UserController::class, 'getOneDokter']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/login-app', [AuthController::class, 'loginApp']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/register', [AuthController::class, 'register']);


//Route::post('masterss', [ApiController::class, 'getMaster']);
Route::post('locationss', [ApiController::class, 'getLocations']);
Route::get('ping', function () {
    return true;
});



Route::post('generalReport/getAll', [GeneralReportSourceMobileController::class, 'getAll']);

Route::post('users/sendNotifications', [UserController::class, 'sendNotificationUser']);

Route::post('dashboards/get-totalLaporanWeb', [DashboardController::class, 'report_perbulanWeb']);
Route::post('dashboards/get-suspekPenyakitWeb', [DashboardController::class, 'suspekPenyakit']);
Route::post('dashboards/get-nonSuspekPenyakitWeb', [DashboardController::class, 'nonSuspekPenyakit']);

Route::post('community-reports/save', [CommunityReportController::class, 'save']);
Route::post('sehatsatli/getReports', [ApiGeneralReportController::class, 'getApi']);
