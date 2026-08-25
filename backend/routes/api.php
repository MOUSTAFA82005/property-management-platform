<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::apiResource('properties', PropertyController::class);
Route::get('properties/{property}/units', [UnitController::class, 'unitsByProperty']);
Route::apiResource('units', UnitController::class);