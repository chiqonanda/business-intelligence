<?php

use Illuminate\Support\Facades\Route;

// API routes yang butuh auth (session-based)
// Catatan: /api/analyst/data sudah didefinisikan di web.php,
// jadi di sini hanya menyimpan route yang benar-benar API-only
Route::middleware('auth')->group(function () {
    // Tidak ada duplikasi — semua route sudah ada di web.php
    // File ini dipertahankan untuk extensibility ke depan
});
