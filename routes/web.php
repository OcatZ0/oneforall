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

        // if (Auth::attempt($credentials)) {
        //     $request->session()->regenerate();
        //     return redirect()->intended(route('dashboard'));
        // }

        if ($credentials['email'] === 'admin' && $credentials['password'] === 'admin') {
            session(['user' => 'admin']);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });

    Route::get('auth/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');

    Route::post('auth/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        // logic kirim email reset password di sini

        return back()->with('status', 'Tautan reset kata sandi telah dikirim ke email Anda.');
    })->name('password.email');
});

Route::group([], function () {
    Route::get('/', function () {
        if (!session('user')) {
            return redirect()->route('login');
        }

        return view('home/index');
    })->name('dashboard');

    Route::get('/agent', function () {
        if (!session('user')) {
            return redirect()->route('login');
        }

        return view('agent/index');
    })->name('agent');

    Route::get('/user', function () {
        if (!session('user')) {
            return redirect()->route('login');
        }

        return view('user/index');
    })->name('user');

    Route::get('/user/{id}/edit', function ($id) {
        if (!session('user')) {
            return redirect()->route('login');
        }

        return view('user/edit-user', compact('id'));
    })->name('edit-user');

    Route::get('/profile', function () {
        if (!session('user')) {
            return redirect()->route('login');
        }

        return view('profile/index');
    })->name('profile');

    Route::post('auth/logout', function (Request $request) {
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

});