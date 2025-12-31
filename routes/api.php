<?php

use App\Http\Controllers\Api\DashboardApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::get('/categories', [DashboardApiController::class, 'categories']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', [DashboardApiController::class, 'currentUser']);
    Route::get('/notifications', [DashboardApiController::class, 'notifications']);

    // Admin endpoints
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [DashboardApiController::class, 'adminStats']);
        Route::get('/skpd-list', [DashboardApiController::class, 'skpdList']);
        Route::get('/activities', [DashboardApiController::class, 'recentActivities']);
    });

    // Operator endpoints
    Route::prefix('operator')->group(function () {
        Route::get('/stats', [DashboardApiController::class, 'operatorStats']);
        Route::get('/pending-contents', [DashboardApiController::class, 'pendingContents']);
    });

    // Publisher endpoints
    Route::prefix('publisher')->group(function () {
        Route::get('/stats', [DashboardApiController::class, 'publisherStats']);
        Route::get('/my-contents', [DashboardApiController::class, 'myContents']);
    });
});
