<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Publisher\ContentController;
use App\Http\Controllers\Admin\SkpdController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Operator\VerificationController;
use App\Http\Controllers\Operator\MonitoringController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// React SPA Route
Route::get('/react', function () {
    return view('react');
})->name('react');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth.custom'])->group(function () {
    
    // Publisher Routes
    Route::middleware(['publisher'])->prefix('publisher')->name('publisher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'publisher'])->name('dashboard');
        Route::resource('content', ContentController::class)->except(['destroy']);
    });

    // Operator Routes
    Route::middleware(['operator'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'operator'])->name('dashboard');
        Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
        Route::get('/verification/history', [VerificationController::class, 'historyIndex'])->name('verification.history.index');
        Route::get('/verification/{content}', [VerificationController::class, 'show'])->name('verification.show');
        Route::get('/verification/{content}/history', [VerificationController::class, 'history'])->name('verification.history');
        Route::post('/verification/{content}/approve', [VerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{content}/reject', [VerificationController::class, 'reject'])->name('verification.reject');
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/{skpd}', [MonitoringController::class, 'show'])->name('monitoring.show');
    });

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('skpd', SkpdController::class);
        Route::resource('user', UserController::class);
        Route::patch('user/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle-status');
        Route::resource('kategori', KategoriController::class);
    });

    // Report Routes (accessible by Operator and Admin)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/content-history', [ReportController::class, 'contentHistory'])->name('content-history');
        Route::get('/skpd-performance', [ReportController::class, 'skpdPerformance'])->name('skpd-performance');
        Route::get('/export/content', [ReportController::class, 'exportContentReport'])->name('export.content');
        Route::get('/export/skpd', [ReportController::class, 'exportSkpdReport'])->name('export.skpd');
        Route::get('/export/dashboard', [ReportController::class, 'exportDashboardReport'])->name('export.dashboard');
    });
});
