<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\CRM\CustomerApiController;
use App\Http\Controllers\Api\Facilities\FacilityApiController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    Route::get('/dashboard/modules', [DashboardApiController::class, 'userModules']);
    
    // CRM API
    Route::prefix('crm')->group(function () {
        Route::apiResource('customers', CustomerApiController::class);
    });
    
    // Facilities API
    Route::prefix('facilities')->group(function () {
        Route::apiResource('facilities', FacilityApiController::class);
    });
});