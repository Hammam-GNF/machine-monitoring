<?php

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
});

require __DIR__.'/settings.php';
