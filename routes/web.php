<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SimperController;
use App\Http\Controllers\FitToWorkController;
use App\Http\Controllers\DailyMonitoringController;
use App\Http\Controllers\EnvSamplingController;
use App\Http\Controllers\WasteDomesticController;
use App\Http\Controllers\WasteLb3Controller;
use App\Http\Controllers\SamplingSubjectController;
use App\Http\Controllers\SamplingParameterController;
use App\Http\Controllers\DepartmentController; 
use App\Http\Controllers\MenuAccessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MyHazardReportController;
use App\Http\Controllers\HazardReportController;
use App\Http\Controllers\SafetyCampaignController;
use App\Http\Controllers\MySafetyCampaignController;
use App\Http\Controllers\HazardCronController;

/*
|--------------------------------------------------------------------------
| Catatan hak akses
|--------------------------------------------------------------------------
| - 'can:menu_xxx.yyy' = dikunci sesuai centang di halaman Akses Menu (config/menus.php).
| - 'role:...'         = aksi khusus role tertentu (misal validasi HSE), tidak diatur dari matriks.
| - Superadmin selalu lolos semua 'can:' lewat Gate::before di AppServiceProvider.
*/

Route::get('/', function () {
    return view('welcome');
});

// Reminder email harian, dipanggil Vercel Cron (dilindungi CRON_SECRET, tanpa login)
Route::get('/cron/hazard-reminders', HazardCronController::class)
    ->middleware('throttle:10,1')
    ->name('cron.hazard-reminders');

// Dashboard: selalu bisa dibuka semua user yang login
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// ==========================================
// --- GRUP ROUTE YANG WAJIB LOGIN (AUTH) ---
// ==========================================
Route::middleware('auth')->group(function () {
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---------------- Document Control ----------------
    // Melihat: sesuai Akses Menu
    Route::get('/documents', [DocumentController::class, 'index'])
        ->middleware('can:menu_documents')
        ->name('documents.index');

    // Upload / kelola dokumen: khusus Superadmin & QHSE
    Route::middleware(['role:superadmin|qhse'])->group(function () {
        Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // ---------------- Master Data ----------------
    Route::resource('sampling-subjects', SamplingSubjectController::class)
        ->middleware('can:menu_master_data.sampling_subject');
    Route::resource('sampling-parameters', SamplingParameterController::class)
        ->middleware('can:menu_master_data.sampling_parameter');

    // ---------------- Request QHSE ----------------
    // Approval PIC Dept untuk SIMPER (tidak dikunci menu, karena dipakai atasan untuk approval)
    Route::put('/simper/{simper}/pic-approval', [SimperController::class, 'updatePicApproval'])->name('simper.pic-approval');

    Route::resource('simper', SimperController::class)
        ->middleware('can:menu_request_qhse.simper');
    Route::resource('fit-to-work', FitToWorkController::class)
        ->middleware('can:menu_request_qhse.fit_to_work');

    // ---------------- Tasklist ----------------
    // Hazard Report: HSE melihat semua, PIC hanya departemennya (diatur di controller)
    Route::middleware('can:menu_tasklist.hazard_report')->group(function () {
        Route::get('/hazard-reports', [HazardReportController::class, 'index'])->name('hazard-report.index');
        Route::get('/hazard-reports/{hazardReport}', [HazardReportController::class, 'show'])->name('hazard-report.show');
        Route::put('/hazard-reports/{hazardReport}/closing', [HazardReportController::class, 'submitClosing'])->name('hazard-report.closing');
        Route::put('/hazard-reports/{hazardReport}/extension', [HazardReportController::class, 'requestExtension'])->name('hazard-report.extension');
    });

    // Hazard Report: aksi khusus HSE (validasi, tolak, verifikasi closing)
    Route::middleware(['role:superadmin|qhse'])->group(function () {
        Route::put('/hazard-reports/{hazardReport}/validate', [HazardReportController::class, 'validateReport'])->name('hazard-report.validate');
        Route::put('/hazard-reports/{hazardReport}/reject', [HazardReportController::class, 'reject'])->name('hazard-report.reject');
        Route::put('/hazard-reports/{hazardReport}/approve-closing', [HazardReportController::class, 'approveClosing'])->name('hazard-report.approve-closing');
        Route::put('/hazard-reports/{hazardReport}/return-closing', [HazardReportController::class, 'returnClosing'])->name('hazard-report.return-closing');
        Route::put('/hazard-reports/{hazardReport}/reject-extension', [HazardReportController::class, 'rejectExtension'])->name('hazard-report.reject-extension');
        // URL sengaja beda dari /hazard-reports/{id} supaya tidak bentrok dengan halaman detail
        Route::get('/hazard-reporters', [HazardReportController::class, 'reporters'])->name('hazard-report.reporters');
    });

    // Safety Campaign: kelola campaign (buat, edit, terbitkan)
    Route::middleware('can:menu_tasklist.safety_campaign')->group(function () {
        Route::put('/safety-campaigns/{safetyCampaign}/publish', [SafetyCampaignController::class, 'togglePublish'])->name('safety-campaigns.publish');
        Route::resource('safety-campaigns', SafetyCampaignController::class)->except('show');
    });

    // ---------------- Environmental ----------------
    Route::resource('daily-monitoring', DailyMonitoringController::class)
        ->middleware('can:menu_environmental.daily_monitoring');

    Route::middleware('can:menu_environmental.sampling')->group(function () {
        Route::get('/env-sampling/get-parameters/{id}', [EnvSamplingController::class, 'getParameters'])->name('env-sampling.get-parameters');
        Route::resource('env-sampling', EnvSamplingController::class);
    });

    Route::middleware('can:menu_environmental.waste_domestic')->group(function () {
        Route::get('/waste-domestic/recap', [WasteDomesticController::class, 'recapDetail'])->name('waste-domestic.recap');
        Route::post('/waste-domestic/recap/upload', [WasteDomesticController::class, 'uploadBast'])->name('waste-domestic.upload-bast');
        Route::post('/waste-domestic/recap/validate', [WasteDomesticController::class, 'validateBast'])->name('waste-domestic.validate-bast');
        Route::resource('waste-domestic', WasteDomesticController::class);
    });

    Route::resource('waste-lb3', WasteLb3Controller::class)
        ->middleware('can:menu_environmental.waste_lb3');

    // ---------------- Personal Record ----------------
    Route::resource('my-hazard-reports', MyHazardReportController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('my-hazard-report')
        ->parameters(['my-hazard-reports' => 'hazardReport'])
        ->middleware('can:menu_personal_record.hazard_report');

    // Safety Campaign untuk karyawan: baca, jawab soal / konfirmasi, komentar
    Route::middleware('can:menu_personal_record.safety_campaign')->group(function () {
        Route::get('/my-safety-campaigns', [MySafetyCampaignController::class, 'index'])->name('my-safety-campaign.index');
        Route::get('/my-safety-campaigns/{safetyCampaign}', [MySafetyCampaignController::class, 'show'])->name('my-safety-campaign.show');
        Route::post('/my-safety-campaigns/{safetyCampaign}/acknowledge', [MySafetyCampaignController::class, 'acknowledge'])->name('my-safety-campaign.acknowledge');
        Route::post('/my-safety-campaigns/{safetyCampaign}/quiz', [MySafetyCampaignController::class, 'submitQuiz'])->name('my-safety-campaign.quiz');
        Route::post('/my-safety-campaigns/{safetyCampaign}/comment', [MySafetyCampaignController::class, 'comment'])->name('my-safety-campaign.comment');
    });

});


// ==========================================
// --- GRUP ROUTE KHUSUS SUPERADMIN ---
// ==========================================
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    
    // Manajemen Data User
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Manajemen Data Role
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Manajemen Data Departemen
    Route::resource('departments', DepartmentController::class);

    // Manajemen Data Perusahaan
    Route::resource('companies', CompanyController::class)->except('show');

    // Manajemen Akses Menu
    Route::get('/menu-access', [MenuAccessController::class, 'index'])->name('menu-access.index');
    Route::post('/menu-access', [MenuAccessController::class, 'update'])->name('menu-access.update');

});

require __DIR__.'/auth.php';