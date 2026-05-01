<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role . '.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $login = trim($credentials['employee_id']);
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_id';

        $remember = $request->boolean('remember');
        if (Auth::attempt([
            $loginField => $login,
            'password' => $credentials['password'],
            'status' => 'active',
        ], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route(Auth::user()->role . '.dashboard'));
        }

        return back()->withErrors([
            'employee_id' => 'Invalid email, employee ID, or password.',
        ])->onlyInput('employee_id');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
