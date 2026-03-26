<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser->isEmployeeAdmin()) {
            $users = User::latest()->paginate(15);
        } else {
            $users = User::whereIn('role', ['employee_base', 'requester'])->latest()->paginate(15);
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        if ($currentUser->isEmployeeAdmin()) {
            $availableRoles = ['employee_manager' => 'Manager'];
        } else {
            $availableRoles = [
                'employee_base' => 'Employee',
                'requester' => 'Requester',
            ];
        }

        return view('users.create', compact('availableRoles'));
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser->isEmployeeAdmin()) {
            $allowedRoles = ['employee_manager'];
        } else {
            $allowedRoles = ['employee_base', 'requester'];
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', $allowedRoles)],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->isEmployeeAdmin() && !in_array($user->role, ['employee_base', 'requester'])) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isEmployeeBase()) {
            $forms = $user->assignedForms()->with('formTemplate')->latest()->get();
        } elseif ($user->isRequester()) {
            $forms = $user->forms()->with('formTemplate')->latest()->get();
        } else {
            $forms = collect();
        }

        return view('users.show', compact('user', 'forms'));
    }

    public function edit(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->isEmployeeAdmin() && !in_array($user->role, ['employee_base', 'requester'])) {
            abort(403, 'Unauthorized.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->isEmployeeAdmin() && !in_array($user->role, ['employee_base', 'requester'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
    }

    public function toggleSuspend(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->isEmployeeAdmin() && !in_array($user->role, ['employee_base', 'requester'])) {
            abort(403, 'Unauthorized.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot suspend yourself.');
        }

        $user->update(['is_suspended' => !$user->is_suspended]);

        $action = $user->is_suspended ? 'suspended' : 'reactivated';

        return back()->with('success', "User {$action} successfully.");
    }
}
