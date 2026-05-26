<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('auth/login', [AuthController::class, 'store']);
});

Route::get('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
Route::post('auth/forgot-password', [AuthController::class, 'sendPasswordReset'])->name('password.email');
Route::get('auth/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : view('landing.index');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout', [DashboardController::class, 'saveLayout'])->name('dashboard.layout');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');

    // Agent routes - available for both admin and customer
    Route::get('/agent', [AgentController::class, 'index'])->name('agent');
    Route::get('/agent/{id}/detail', [AgentController::class, 'detail'])->name('agent.detail');
    Route::get('/agent/{id}/security-events', [AgentController::class, 'securityEvents'])->name('agent.security-events');
    Route::get('/agent/{id}/integrity-monitoring', [AgentController::class, 'integrityMonitoring'])->name('agent.integrity-monitoring');
    Route::get('/agent/{id}/sca', [AgentController::class, 'sca'])->name('agent.sca');
    Route::get('/agent/{id}/vulnerabilities', [AgentController::class, 'vulnerabilities'])->name('agent.vulnerabilities');
    Route::get('/agent/{id}/mitre-attack', [AgentController::class, 'mitreAttack'])->name('agent.mitre-attack');
    Route::get('/agent/{id}/security-events/alerts', [AgentController::class, 'getSeAlerts'])->name('agent.se.alerts');
    Route::get('/agent/{id}/security-events/groups', [AgentController::class, 'getSeGroups'])->name('agent.se.groups');
    Route::get('/agent/{id}/integrity-monitoring/events', [AgentController::class, 'getIntegrityEvents'])->name('agent.fim.events');
    Route::get('/agent/{id}/sca/checks', [AgentController::class, 'getScaChecksJson'])->name('agent.sca.checks');
    Route::get('/agent/{id}/mitre-attack/alerts', [AgentController::class, 'getMitreAlertsJson'])->name('agent.mitre.alerts');
    Route::get('/agent/{id}/compliance', [AgentController::class, 'compliance'])->name('agent.compliance');
    Route::get('/agent/chart-data', [AgentController::class, 'getChartData'])->name('agent.chart-data');
    Route::get('/agent/{id}/chart-data', [AgentController::class, 'getDetailChartData'])->name('agent.detail-chart-data');
    Route::post('/agent/sync', [AgentController::class, 'syncAgentsFromWazuh'])->name('agent.sync');

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::get('/user', [UserController::class, 'index'])->name('user');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('edit-user');
        Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
    });
});
