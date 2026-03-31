<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email or username
        $user = User::where('email', $credentials['email'])
                    ->orWhere('username', $credentials['email'])
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'User tidak ditemukan.',
            ])->onlyInput('email');
        }

        // Check password and authenticate
        if (Auth::attempt(['email' => $user->email, 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Log activity
            LogActivity::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas' => 'User login',
            ]);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Logic for sending password reset email goes here

        return back()->with('status', 'Tautan reset kata sandi telah dikirim ke email Anda.');
    }

    public function logout(Request $request)
    {
        // Log activity before logout
        if (Auth::check()) {
            LogActivity::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas' => 'User logout',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
