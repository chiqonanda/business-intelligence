<?php

use App\Http\Controllers\AnalystController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Semua API route butuh auth (session-based via Sanctum atau web guard)
Route::middleware('auth')->group(function () {

    // ── Dashboard summary ─────────────────────────────────────────────────────
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // ── Chart data (hanya analyst & super_admin) ──────────────────────────────
    Route::middleware('role:super_admin,analyst')->group(function () {
        Route::get('/chart/revenue-trend', [AnalystController::class, 'revenueTrend']);
        Route::get('/chart/top-products',  [AnalystController::class, 'topProducts']);
        Route::get('/chart/region-split',  [AnalystController::class, 'regionSplit']);
        Route::get('/chart/gender-split',  [AnalystController::class, 'genderSplit']);
        Route::get('/chart/channel-split', [AnalystController::class, 'channelSplit']);
        Route::get('/transactions',        [AnalystController::class, 'transactions']);
    });
});