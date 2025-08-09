<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Housing\PropertyController;
use App\Http\Controllers\Housing\HousingApplicationController;
use App\Http\Controllers\Housing\WaitingListController;
use App\Services\DebugAgent;

// Installation routes
Route::middleware(['web'])->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::get('/install/step1', [InstallController::class, 'step1'])->name('install.step1');
    Route::get('/install/step2', [InstallController::class, 'step2'])->name('install.step2');
    Route::post('/install/step2', [InstallController::class, 'storeStep2'])->name('install.step2.store');
    Route::get('/install/step3', [InstallController::class, 'step3'])->name('install.step3');
    Route::post('/install/complete', [InstallController::class, 'completeInstallation'])->name('install.complete');
    Route::get('/install/complete', [InstallController::class, 'complete'])->name('install.complete.view');
});

// Debug routes (only in debug mode)
if (config('app.debug')) {
    Route::get('/debug/status', function (DebugAgent $debugAgent) {
        return response()->json($debugAgent->generateReport());
    })->name('debug.status');

    Route::get('/debug/install', function (DebugAgent $debugAgent) {
        return response()->json($debugAgent->checkInstallation());
    })->name('debug.install');

    Route::get('/debug/database', function (DebugAgent $debugAgent) {
        return response()->json($debugAgent->checkDatabase());
    })->name('debug.database');

    Route::get('/debug/permissions', function (DebugAgent $debugAgent) {
        return response()->json($debugAgent->checkPermissions());
    })->name('debug.permissions');
}

// Root route - redirect to install if not installed, otherwise to dashboard
Route::get('/', function () {
    if (!file_exists(storage_path('app/installed.lock'))) {
        return redirect('/install');
    }
    return redirect('/dashboard');
});

// Check if installed before loading other routes
Route::middleware(['ensure.installed'])->group(function () {
    // Authentication routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin routes
        Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class);
        });

        // Housing routes
        Route::prefix('housing')->name('housing.')->group(function () {
            Route::resource('properties', PropertyController::class);
            Route::resource('applications', HousingApplicationController::class);
            Route::resource('waiting-list', WaitingListController::class);
        });
    });
});

// Fallback route for debugging
Route::fallback(function () {
    if (!file_exists(storage_path('app/installed.lock'))) {
        return redirect('/install');
    }
    abort(404);
});