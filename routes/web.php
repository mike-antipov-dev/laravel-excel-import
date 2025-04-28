<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ApiDataController;

Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'upload'])->name('import.upload');

Route::get('/api/data', [ApiDataController::class, 'showData'])->name('api.data');
