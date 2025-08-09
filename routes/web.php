<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Housing\HousingApplicationController;
use App\Http\Controllers\Housing\WaitingListController;
use App\Http\Controllers\Housing\PropertyController;

// Welcome route (will redirect to install if needed)
Route::get('/', function () {
    // Check if installation is complete
    if (!file_exists(storage_path('app/installed.lock'))) {
        return redirect('/install');
    }
    return redirect('/dashboard');
});

// Installation routes
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::get('/step1', [InstallController::class, 'step1'])->name('install.step1');
    Route::get('/step2', [InstallController::class, 'step2'])->name('install.step2');
    Route::post('/step2', [InstallController::class, 'storeStep2'])->name('install.step2.store');
    Route::get('/step3', [InstallController::class, 'step3'])->name('install.step3');
    Route::post('/complete', [InstallController::class, 'completeInstallation'])->name('install.complete');
    Route::get('/complete', [InstallController::class, 'complete'])->name('install.complete.view');
    Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('install.test-database');
});

// Main application routes (protected by installation middleware)
Route::middleware('ensure.installation')->group(function () {
    // Authentication routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (requires authentication)
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Admin routes
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
            Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        });

        // Housing routes
        Route::prefix('housing')->group(function () {
            Route::get('/applications', [\App\Http\Controllers\Housing\HousingApplicationController::class, 'index'])->name('housing.applications.index');
            Route::get('/waiting-list', [\App\Http\Controllers\Housing\WaitingListController::class, 'index'])->name('housing.waiting-list.index');
        });
    });
});