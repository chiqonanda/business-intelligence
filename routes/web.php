<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalystController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard publik
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Analyst
    Route::middleware('role:super_admin,analyst')->group(function () {
        Route::get('/dashboard/analytics', [AnalystController::class, 'index'])->name('analyst.index');
        Route::get('/api/analyst/data', [AnalystController::class, 'apiData'])->name('analyst.data');
        Route::get('/analyst/export', [AnalystController::class, 'export'])->name('analyst.export');
    });

    // Manager
    Route::middleware('role:super_admin,manager')->group(function () {
        Route::get('/dashboard/insights', [ManagerController::class, 'index'])->name('manager.index');
    });

    // Staff - Upload
    Route::middleware('role:super_admin,staff')->group(function () {
        Route::get('/dashboard/upload', [UploadController::class, 'index'])->name('upload.index');
        Route::post('/dashboard/upload', [UploadController::class, 'store'])->name('upload.store');
        Route::delete('/dashboard/upload/reset', [UploadController::class, 'truncate'])->name('upload.reset');
        Route::delete('/dashboard/upload/{filename}', [UploadController::class, 'destroyFile'])->name('upload.destroy');
    });

    // Super Admin - User Management
    // Route names must match exactly what Admin.vue uses:
    //   admin.index, admin.store, admin.updateRole, admin.destroyUser
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/dashboard/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/dashboard/admin/users', [AdminController::class, 'store'])->name('admin.store');
        Route::patch('/dashboard/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.updateRole');
        Route::delete('/dashboard/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.destroyUser');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
