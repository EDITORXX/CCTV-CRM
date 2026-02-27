<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        try {
            $company = Company::findOrFail(session('current_company_id'));
            $users = $company->users()->orderBy('company_user.created_at', 'desc')->paginate(20);
            return response()->view('users.index', compact('users'));
        } catch (\Throwable $e) {
            report($e);
            $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return response("<html><head><meta charset='utf-8'><title>Error</title></head><body><h1>Error</h1><p>{$msg}</p><p><a href='/users'>Back to Users</a></p></body></html>", 500);
        }
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:company_admin,manager,accountant,technician,customer',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Ensure role exists (e.g. if RoleSeeder was not run) then assign
            Role::firstOrCreate(['name' => $validated['role'], 'guard_name' => 'web']);
            $user->assignRole($validated['role']);

            $company = Company::findOrFail(session('current_company_id'));
            $company->users()->syncWithoutDetaching([$user->id => ['role' => $validated['role']]]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.')
                ->with('created_user_name', $user->name)
                ->with('created_user_email', $user->email)
                ->with('created_user_password', $validated['password']);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Could not create user. ' . ($request->has('debug') ? $e->getMessage() : 'Please try again.'));
        }
    }

    public function edit(User $user)
    {
        $companyId = session('current_company_id');
        $pivot = $user->companies()->where('companies.id', $companyId)->first();
        $role = $pivot ? $pivot->pivot->role : '';

        return view('users.edit', compact('user', 'role'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:company_admin,manager,accountant,technician,customer',
            'is_active' => 'boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $user->syncRoles([$validated['role']]);

        $company = Company::findOrFail(session('current_company_id'));
        $company->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $company = Company::findOrFail(session('current_company_id'));
        $company->users()->detach($user->id);

        return redirect()->route('users.index')->with('success', 'User removed from company.');
    }
}
