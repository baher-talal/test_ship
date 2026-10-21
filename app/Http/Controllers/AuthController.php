<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $attempt = Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => 1],
            $request->boolean('remember')
        );

        if (! $attempt) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Invalid credentials or the account is suspended').'.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
