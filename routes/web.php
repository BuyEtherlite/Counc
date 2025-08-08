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

// New module controllers
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\Security\AuditController;
use App\Http\Controllers\Facilities\FacilityController;
use App\Http\Controllers\Facilities\BookingController;
use App\Http\Controllers\Recreation\SwimmingPoolController;
use App\Http\Controllers\Recreation\GateTakingController;
use App\Http\Controllers\Cemetery\CemeteryController;
use App\Http\Controllers\Cemetery\GraveController;
use App\Http\Controllers\Property\PropertyValuationController;
use App\Http\Controllers\Property\LeaseController;
use App\Http\Controllers\Property\LandController;
use App\Http\Controllers\Planning\PlanningController;
use App\Http\Controllers\Architecture\ArchitectureController;
use App\Http\Controllers\Utilities\WaterController;
use App\Http\Controllers\QualityAssurance\QualityController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Health\HealthController;
use App\Http\Controllers\Emergency\EmergencyController;
use App\Http\Controllers\Committee\CommitteeController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Installation routes
Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        Route::resource('departments', DepartmentController::class);
        Route::resource('offices', OfficeController::class);
    });

    // Housing Management routes
    Route::prefix('housing')->name('housing.')->group(function () {
        // Housing Applications
        Route::resource('applications', HousingApplicationController::class);
        Route::post('applications/{application}/assess', [HousingApplicationController::class, 'assess'])->name('applications.assess');
        
        // Waiting List
        Route::resource('waiting-list', WaitingListController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);
        Route::post('waiting-list/{waitingList}/contact', [WaitingListController::class, 'contact'])->name('waiting-list.contact');
        Route::patch('waiting-list/{waitingList}/status', [WaitingListController::class, 'updateStatus'])->name('waiting-list.update-status');
        Route::patch('waiting-list/{waitingList}/priority', [WaitingListController::class, 'updatePriority'])->name('waiting-list.update-priority');
        Route::post('waiting-list/bulk-contact', [WaitingListController::class, 'bulkContact'])->name('waiting-list.bulk-contact');
        Route::get('waiting-list-recalculate', [WaitingListController::class, 'recalculatePositions'])->name('waiting-list.recalculate');
        
        // Properties
        Route::resource('properties', PropertyController::class);
        Route::patch('properties/{property}/status', [PropertyController::class, 'updateStatus'])->name('properties.update-status');
        Route::post('properties/{property}/inspection', [PropertyController::class, 'scheduleInspection'])->name('properties.schedule-inspection');
        Route::get('properties-available', [PropertyController::class, 'available'])->name('properties.available');
    });

    // CRM Routes
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('contacts', ContactController::class);
    });

    // Security & Audit Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::resource('audit', AuditController::class, ['only' => ['index', 'show']]);
    });

    // Facilities Management Routes
    Route::prefix('facilities')->name('facilities.')->group(function () {
        Route::resource('facilities', FacilityController::class);
        Route::resource('bookings', BookingController::class);
    });

    // Recreation Routes
    Route::prefix('recreation')->name('recreation.')->group(function () {
        Route::resource('swimming-pools', SwimmingPoolController::class);
        Route::resource('gate-takings', GateTakingController::class);
    });

    // Cemetery Routes
    Route::prefix('cemetery')->name('cemetery.')->group(function () {
        Route::resource('cemeteries', CemeteryController::class);
        Route::resource('graves', GraveController::class);
    });

    // Property Management Routes
    Route::prefix('property')->name('property.')->group(function () {
        Route::resource('valuations', PropertyValuationController::class);
        Route::resource('leases', LeaseController::class);
        Route::resource('land', LandController::class);
    });

    // Planning Routes
    Route::prefix('planning')->name('planning.')->group(function () {
        Route::resource('applications', PlanningController::class);
    });

    // Architecture Routes
    Route::prefix('architecture')->name('architecture.')->group(function () {
        Route::resource('projects', ArchitectureController::class);
    });

    // Utilities Routes
    Route::prefix('utilities')->name('utilities.')->group(function () {
        Route::resource('water', WaterController::class);
    });

    // Quality Assurance Routes
    Route::prefix('quality')->name('quality.')->group(function () {
        Route::resource('checks', QualityController::class);
    });

    // Finance Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::resource('accounts', FinanceController::class);
    });

    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::resource('bills', BillingController::class);
    });

    // Inventory Routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('items', InventoryController::class);
    });

    // Health Routes
    Route::prefix('health')->name('health.')->group(function () {
        Route::resource('inspections', HealthController::class);
    });

    // Emergency Routes
    Route::prefix('emergency')->name('emergency.')->group(function () {
        Route::resource('services', EmergencyController::class);
    });

    // Committee Routes
    Route::prefix('committee')->name('committee.')->group(function () {
        Route::resource('committees', CommitteeController::class);
    });
});