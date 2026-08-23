<?php

use App\Http\Controllers\Api\SensorDataController;
use Illuminate\Support\Facades\Route;

Route::post('sensor-data', [SensorDataController::class, 'store'])
    ->name('api.sensor-data.store');
