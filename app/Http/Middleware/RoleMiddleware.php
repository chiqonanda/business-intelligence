<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Penggunaan di web.php:
     *   ->middleware('role:super_admin')
     *   ->middleware('role:super_admin,analyst')   ← multiple roles dipisah koma
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Cek apakah role user ada di daftar roles yang diizinkan
        if (! in_array($request->user()->role, $roles)) {
            // Kembalikan 403 dengan view khusus (buat resources/views/errors/403.blade.php)
            abort(403, 'Akses ditolak. Role Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}