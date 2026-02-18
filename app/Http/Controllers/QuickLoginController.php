<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickLoginController extends Controller
{
    public function index()
    {
        $users = User::select('users.*', 'company_user.role', 'companies.name as company_name')
            ->join('company_user', 'users.id', '=', 'company_user.user_id')
            ->join('companies', 'companies.id', '=', 'company_user.company_id')
            ->where('users.is_active', true)
            ->orderByRaw("FIELD(company_user.role, 'company_admin', 'manager', 'accountant', 'technician', 'customer')")
            ->get();

        return view('auth.quick-login', compact('users'));
    }

    public function login(User $user)
    {
        Auth::login($user);

        $companies = $user->companies;
        if ($companies->count() === 1) {
            session(['current_company_id' => $companies->first()->id]);
            return redirect()->route('dashboard');
        }

        return redirect()->route('company.select');
    }
}
