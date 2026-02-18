<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $company = Company::findOrFail(session('current_company_id'));
        $users = $company->users()->paginate(20);

        return view('users.index', compact('users'));
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $company = Company::findOrFail(session('current_company_id'));
        $company->users()->attach($user->id, ['role' => $validated['role']]);

        return redirect()->route('users.index')->with('success', 'User created and added to company.');
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
