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

// Installation routes (available before installation is complete)
Route::group(['prefix' => 'install'], function () {
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

// Check if installed before loading other routes
Route::middleware(['ensure.installed'])->group(function () {
    // Authentication routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

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
Route::get('/{any}', function () {
    if (!file_exists(storage_path('app/installed.lock'))) {
        return redirect('/install');
    }
    abort(404);
})->where('any', '.*');
