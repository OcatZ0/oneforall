<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])
                    ->orWhere('username', $credentials['email'])
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Username atau Password salah.',
            ])->onlyInput('email');
        }

        // Check password and authenticate
        if (Auth::attempt(['email' => $user->email, 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Log activity
            LogActivity::create([
                'user_id'  => Auth::user()->id,
                'activity' => 'User login',
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

    public function sendPasswordReset(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->validated()['email'])->first();

        if ($user) {
            $token = Str::random(64);

            PasswordResetToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $request->validated()['email']], false));

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

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $record = PasswordResetToken::where('user_id', $user->id)->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            return back()->withErrors(['email' => 'Token reset tidak valid.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            PasswordResetToken::where('user_id', $user->id)->delete();
            return back()->withErrors(['email' => 'Token reset sudah kedaluwarsa. Silakan minta ulang.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        PasswordResetToken::where('user_id', $user->id)->delete();

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk.');
    }

    public function logout(Request $request)
    {
        // Log activity before logout
        if (Auth::check()) {
            LogActivity::create([
                'user_id'  => Auth::user()->id,
                'activity' => 'User logout',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
