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
});