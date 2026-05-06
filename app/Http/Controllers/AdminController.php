<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Admin', [
            'users' => User::orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function users()
    {
        return response()->json(
            User::orderBy('role')->orderBy('name')->get()
        );
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:super_admin,analyst,manager,staff'],
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', "Role {$user->name} berhasil diubah ke {$request->role}.");
    }

    public function destroyUser(User $user)
    {
        // Jangan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();

        return back()->with('success', "User {$user->name} berhasil dihapus.");
    }
}