<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('auth/login', [AuthController::class, 'store']);

    Route::get('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('auth/forgot-password', [AuthController::class, 'sendPasswordReset'])->name('password.email');
});

Route::middleware('auth.session')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/agent', [AgentController::class, 'index'])->name('agent');
    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('edit-user');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');
});
