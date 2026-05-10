<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class AdminController extends Controller
{
    private array $roles = ['super_admin', 'analyst', 'manager', 'staff'];

    public function index()
    {
        $users = User::orderBy('created_at', 'asc')->get();

        return Inertia::render('Dashboard/Admin', [
            'users' => $users,
            'roles' => $this->roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'role'     => ['required', 'in:' . implode(',', $this->roles)],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'Operator deployed successfully.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:' . implode(',', $this->roles)],
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id && $request->role !== 'super_admin') {
            return back()->with('error', 'Cannot change your own role.');
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyUser(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'Operator terminated.');
    }
}
