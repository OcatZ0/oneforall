<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
            'email' => 'Username atau Password salah.',
        ])->onlyInput('email');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['id_pengguna' => $user->id_pengguna],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $request->email], false));

            Mail::to($request->email)->send(new ResetPasswordMail($resetUrl, $user->username));
        }

        return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi telah dikirim.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $record = DB::table('password_reset_tokens')->where('id_pengguna', $user->id_pengguna)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token reset tidak valid.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('id_pengguna', $user->id_pengguna)->delete();
            return back()->withErrors(['email' => 'Token reset sudah kedaluwarsa. Silakan minta ulang.']);
        }

        $user->kata_sandi = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('id_pengguna', $user->id_pengguna)->delete();

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk.');
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
