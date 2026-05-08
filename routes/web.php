<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalystController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard publik dengan informasi terbatas
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Dashboard Analyst (super_admin juga bisa akses)
    Route::middleware('role:super_admin,analyst')->group(function () {
        Route::get('/dashboard/analytics', [AnalystController::class, 'index'])->name('analyst.index');
        Route::get('/api/analyst/data', [AnalystController::class, 'apiData'])->name('analyst.data');
        Route::get('/analyst/export', [AnalystController::class, 'export'])->name('analyst.export');
    });

    // Dashboard Manager (super_admin juga bisa akses)
    Route::middleware('role:super_admin,manager')->group(function () {
        Route::get('/dashboard/insights', [ManagerController::class, 'index'])->name('manager.index');
    });

    // Dashboard Staff - Upload CSV (super_admin juga bisa akses)
    Route::middleware('role:super_admin,staff')->group(function () {
        Route::get('/dashboard/upload', [UploadController::class, 'index'])->name('upload.index');
        Route::post('/dashboard/upload', [UploadController::class, 'store'])->name('upload.store');
        Route::delete('/dashboard/upload/reset', [UploadController::class, 'truncate'])->name('upload.reset');
        Route::delete('/dashboard/upload/{filename}', [UploadController::class, 'destroyFile'])->name('upload.destroy');
    });

    // Dashboard Super Admin - User Management
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/dashboard/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/dashboard/admin/users', [AdminController::class, 'store'])->name('admin.users.store');
        Route::patch('/dashboard/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
        Route::delete('/dashboard/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
