<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Google authentication failed. Please try again.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'No account found with this email address. Please contact your administrator.');
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->with('error', 'Your account is deactivated. Please contact your administrator.');
        }

        $user->update(['google_id' => $googleUser->getId()]);

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
