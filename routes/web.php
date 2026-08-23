<?php

use App\Http\Controllers\MachineController;
use App\Http\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('admin', function () {
        return 'Admin access granted.';
    })->middleware('role:admin');

    Route::get('viewer', function () {
        return 'Viewer access granted.';
    })->middleware('role:viewer');

    Route::get('admin-or-viewer', function () {
        return 'Admin or Viewer access granted.';
    })->middleware('role:admin,viewer');

    Route::get('machines', [MachineController::class, 'index'])
        ->name('machines.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('machines/create', [MachineController::class, 'create'])
            ->name('machines.create');

        Route::post('machines', [MachineController::class, 'store'])
            ->name('machines.store');

        Route::get('machines/{machine}/edit', [MachineController::class, 'edit'])
            ->name('machines.edit');

        Route::put('machines/{machine}', [MachineController::class, 'update'])
            ->name('machines.update');
    });

    Route::get('machines/{machine}', [MachineController::class, 'show'])
        ->name('machines.show');

    Route::get('sensors', [SensorController::class, 'index'])
    ->name('sensors.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('sensors/create', [SensorController::class, 'create'])
            ->name('sensors.create');

        Route::post('sensors', [SensorController::class, 'store'])
            ->name('sensors.store');

        Route::get('sensors/{sensor}/edit', [SensorController::class, 'edit'])
            ->name('sensors.edit');

        Route::put('sensors/{sensor}', [SensorController::class, 'update'])
            ->name('sensors.update');
    });

    Route::get('sensors/{sensor}', [SensorController::class, 'show'])
        ->name('sensors.show');
});

require __DIR__.'/settings.php';
