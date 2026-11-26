<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/peta', [LandingController::class, 'map'])->name('landing.map');
Route::get('/peta/titik', [LandingController::class, 'mapPoints'])->name('landing.map.points');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('dashboard');

        // Backup database (.sql)
        Route::get('backup/database', [BackupController::class, 'database'])->name('backup.database');

        // Users (Super Admin only)
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('super_admin');

        // Provider (Data Provider/Utilitas)
        Route::resource('provider', ProviderController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('provider/export', [ProviderController::class, 'export'])->name('provider.export');
        Route::get('provider/import/template', [ProviderController::class, 'importTemplate'])->name('provider.import.template');
        Route::post('provider/import/preview', [ProviderController::class, 'importPreview'])->name('provider.import.preview');
        Route::post('provider/import/commit', [ProviderController::class, 'importCommit'])->name('provider.import.commit');
        Route::get('provider/import/errors/{token}', [ProviderController::class, 'importErrors'])->name('provider.import.errors');
    });
});

// Auth routes
require __DIR__ . '/auth.php';
