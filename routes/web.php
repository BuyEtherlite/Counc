<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Housing\PropertyController;
use App\Http\Controllers\Housing\HousingApplicationController;
use App\Http\Controllers\Housing\WaitingListController;
use App\Services\DebugAgent;

// Installation routes
Route::middleware(['web'])->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::post('/install', [InstallController::class, 'store'])->name('install.store');
    Route::post('/install/test-database', [InstallController::class, 'testDatabase'])->name('install.test-database');
    Route::get('/install/complete', [InstallController::class, 'complete'])->name('install.complete');
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

// Authentication routes (available regardless of installation status)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Check if installed before loading other routes
Route::middleware(['ensure.installed'])->group(function () {

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin routes
        Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('departments', DepartmentController::class);
            Route::resource('offices', OfficeController::class);
        });

        // Housing routes
        Route::prefix('housing')->name('housing.')->group(function () {
            Route::resource('applications', HousingApplicationController::class);
            Route::resource('properties', PropertyController::class);
            Route::get('/waiting-list', [WaitingListController::class, 'index'])->name('waiting-list.index');
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