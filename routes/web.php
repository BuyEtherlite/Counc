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

        // Administration CRM routes
        Route::prefix('administration')->name('administration.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Administration\CrmController::class, 'index'])->name('index');
            Route::get('/customers', [\App\Http\Controllers\Administration\CrmController::class, 'customers'])->name('customers');
            Route::get('/service-requests', [\App\Http\Controllers\Administration\CrmController::class, 'serviceRequests'])->name('service-requests');
            Route::get('/communications', [\App\Http\Controllers\Administration\CrmController::class, 'communications'])->name('communications');
        });

        // Facilities routes
        Route::prefix('facilities')->name('facilities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Facilities\BookingController::class, 'index'])->name('index');
            Route::get('/pools', [\App\Http\Controllers\Facilities\BookingController::class, 'pools'])->name('pools');
            Route::get('/halls', [\App\Http\Controllers\Facilities\BookingController::class, 'halls'])->name('halls');
            Route::get('/sports', [\App\Http\Controllers\Facilities\BookingController::class, 'sports'])->name('sports');
        });

        // Cemeteries routes
        Route::prefix('cemeteries')->name('cemeteries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Cemeteries\CemeteryController::class, 'index'])->name('index');
            Route::get('/grave-register', [\App\Http\Controllers\Cemeteries\CemeteryController::class, 'graveRegister'])->name('grave-register');
            Route::get('/burials', [\App\Http\Controllers\Cemeteries\CemeteryController::class, 'burials'])->name('burials');
            Route::get('/maintenance', [\App\Http\Controllers\Cemeteries\CemeteryController::class, 'maintenance'])->name('maintenance');
        });

        // Property Management routes
        Route::prefix('property-management')->name('property.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Property\PropertyManagementController::class, 'index'])->name('index');
            Route::get('/valuations', [\App\Http\Controllers\Property\PropertyManagementController::class, 'valuations'])->name('valuations');
            Route::get('/leases', [\App\Http\Controllers\Property\PropertyManagementController::class, 'leases'])->name('leases');
            Route::get('/land-records', [\App\Http\Controllers\Property\PropertyManagementController::class, 'landRecords'])->name('land-records');
        });

        // Planning routes
        Route::prefix('planning')->name('planning.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Planning\PlanningController::class, 'index'])->name('index');
            Route::get('/applications', [\App\Http\Controllers\Planning\PlanningController::class, 'applications'])->name('applications');
            Route::get('/approvals', [\App\Http\Controllers\Planning\PlanningController::class, 'approvals'])->name('approvals');
            Route::get('/zoning', [\App\Http\Controllers\Planning\PlanningController::class, 'zoning'])->name('zoning');
        });

        // Water Management routes
        Route::prefix('water')->name('water.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Water\WaterController::class, 'index'])->name('index');
            Route::get('/connections', [\App\Http\Controllers\Water\WaterController::class, 'connections'])->name('connections');
            Route::get('/metering', [\App\Http\Controllers\Water\WaterController::class, 'metering'])->name('metering');
            Route::get('/billing', [\App\Http\Controllers\Water\WaterController::class, 'billing'])->name('billing');
        });

        // Finance routes
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Finance\FinanceController::class, 'index'])->name('index');
            Route::get('/budget', [\App\Http\Controllers\Finance\FinanceController::class, 'budget'])->name('budget');
            Route::get('/revenue', [\App\Http\Controllers\Finance\FinanceController::class, 'revenue'])->name('revenue');
            Route::get('/expenses', [\App\Http\Controllers\Finance\FinanceController::class, 'expenses'])->name('expenses');
            Route::get('/reports', [\App\Http\Controllers\Finance\FinanceController::class, 'reports'])->name('reports');
        });

        // Inventory routes
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Inventory\InventoryController::class, 'index'])->name('index');
            Route::get('/items', [\App\Http\Controllers\Inventory\InventoryController::class, 'items'])->name('items');
            Route::get('/stock', [\App\Http\Controllers\Inventory\InventoryController::class, 'stock'])->name('stock');
            Route::get('/suppliers', [\App\Http\Controllers\Inventory\InventoryController::class, 'suppliers'])->name('suppliers');
        });

        // Committee Administration routes
        Route::prefix('committee')->name('committee.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Committee\CommitteeController::class, 'index'])->name('index');
            Route::get('/members', [\App\Http\Controllers\Committee\CommitteeController::class, 'members'])->name('members');
            Route::get('/meetings', [\App\Http\Controllers\Committee\CommitteeController::class, 'meetings'])->name('meetings');
            Route::get('/minutes', [\App\Http\Controllers\Committee\CommitteeController::class, 'minutes'])->name('minutes');
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