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


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// ==========================================
// --- GRUP ROUTE YANG WAJIB LOGIN (AUTH) ---
// ==========================================
Route::middleware('auth')->group(function () {
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Document Control (Semua user login bisa melihat)
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    
    // Document Control & Master Data (Hanya Superadmin & QHSE yang bisa Upload/Kelola)
    Route::middleware(['role:superadmin|qhse'])->group(function () {
        
        // --- Document Control ---
        Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // --- Master Data Sampling Lingkungan ---
        Route::resource('sampling-subjects', SamplingSubjectController::class);
        Route::resource('sampling-parameters', SamplingParameterController::class);
        
    });

    // Request QHSE - SIMPER
    // Rute khusus untuk Approval PIC Dept (Atasan)
    Route::put('/simper/{simper}/pic-approval', [SimperController::class, 'updatePicApproval'])->name('simper.pic-approval');
    
    Route::resource('simper', SimperController::class);
    Route::resource('fit-to-work', FitToWorkController::class);
    
    // Environmental Monitoring - Daily Monitoring
    Route::resource('daily-monitoring', DailyMonitoringController::class);
    
    // Environmental Monitoring - Sampling
    Route::get('/env-sampling/get-parameters/{id}', [EnvSamplingController::class, 'getParameters'])->name('env-sampling.get-parameters');
    Route::resource('env-sampling', EnvSamplingController::class);
    
    // Environmental Monitoring - Waste Management (Sampah Domestik)
    Route::get('/waste-domestic/recap', [WasteDomesticController::class, 'recapDetail'])->name('waste-domestic.recap');
    Route::post('/waste-domestic/recap/upload', [WasteDomesticController::class, 'uploadBast'])->name('waste-domestic.upload-bast');
    Route::post('/waste-domestic/recap/validate', [WasteDomesticController::class, 'validateBast'])->name('waste-domestic.validate-bast');
    Route::resource('waste-domestic', WasteDomesticController::class);
    
    // Environmental Monitoring - Waste Management (LB3)
    Route::resource('waste-lb3', WasteLb3Controller::class);
    
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

});

require __DIR__.'/auth.php';