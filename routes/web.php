<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/install', 'App\Http\Controllers\InstallController@index')->name('install.index');
Route::post('/install', 'App\Http\Controllers\InstallController@store')->name('install.store');