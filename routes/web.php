<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/peta', [LandingController::class, 'map'])->name('landing.map');

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

        // Users (Super Admin only)
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('super_admin');

        // Provider (Data Provider/Utilitas)
        Route::resource('provider', ProviderController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('provider/export', [ProviderController::class, 'export'])->name('provider.export');
        Route::post('provider/import', [ProviderController::class, 'import'])->name('provider.import');
    });
});

// Auth routes
require __DIR__ . '/auth.php';
