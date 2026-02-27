<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickLoginController extends Controller
{
    public function index()
    {
        $users = collect();

        try {
            // Show all active users (with or without company) so list is never empty if users exist
            $users = User::where('users.is_active', true)
                ->with('companies')
                ->orderBy('name')
                ->get();
        } catch (\Throwable $e) {
            report($e);
        }

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
