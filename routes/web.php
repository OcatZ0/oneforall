<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::middleware('guest')->group(function () {
    Route::get('auth/login', fn() => view('auth.login'))->name('login');

    Route::post('auth/login', function (Request $request) {
        $credentials = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });

    Route::get('auth/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => view('dashboard'))->name('dashboard');

    Route::post('auth/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
