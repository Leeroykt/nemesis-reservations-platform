<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\TableController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

// Auth routes – session-based (NOT prefixed with api/v1)
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');

// Inertia login page
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

// Protected API routes (with session support)
Route::middleware(['auth:sanctum'])->prefix('api/v1')->group(function () {

    // ✅ MOVED: /me endpoint now under /api/v1 prefix
    Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

    // Role test route
    Route::get('/role-test', function () {
        return response()->json(['message' => 'Access granted.']);
    })->middleware('role:owner');

    // Host+ routes
    Route::middleware('role:host')->group(function () {
        Route::get('/dashboard/kpis', [DashboardController::class, 'kpis'])->name('api.v1.dashboard.kpis');
        Route::get('/dashboard/revenue-trend', [DashboardController::class, 'revenueTrend'])->name('api.v1.dashboard.revenue-trend');
        Route::get('/dashboard/status-breakdown', [DashboardController::class, 'statusBreakdown'])->name('api.v1.dashboard.status-breakdown');
        Route::get('/activity', [DashboardController::class, 'activity'])->name('api.v1.activity');

        Route::get('/reservations', [ReservationController::class, 'index'])->name('api.v1.reservations.index');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('api.v1.reservations.store');
        Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('api.v1.reservations.show');
        Route::patch('/reservations/{id}', [ReservationController::class, 'update'])->name('api.v1.reservations.update');
        Route::post('/reservations/bulk', [ReservationController::class, 'bulk'])->name('api.v1.reservations.bulk');

        Route::get('/tables', [TableController::class, 'index'])->name('api.v1.tables.index');
        Route::patch('/tables/{id}/status', [TableController::class, 'updateStatus'])->name('api.v1.tables.status');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('api.v1.notifications.mark-all-read');
        Route::patch('/notifications/{id}', [NotificationController::class, 'markRead'])->name('api.v1.notifications.mark-read');

        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])
            ->middleware('role:manager')
            ->name('api.v1.reservations.destroy');
    });

    // Manager+ routes
    Route::middleware('role:manager')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('api.v1.customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('api.v1.customers.show');

        Route::get('/analytics/peak-hours', [AnalyticsController::class, 'peakHours'])->name('api.v1.analytics.peak-hours');
        Route::get('/analytics/popular-tables', [AnalyticsController::class, 'popularTables'])->name('api.v1.analytics.popular-tables');
        Route::get('/analytics/customer-growth', [AnalyticsController::class, 'customerGrowth'])->name('api.v1.analytics.customer-growth');
    });
});

// Protected dashboard routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Overview');
    })->name('dashboard');

    Route::get('/dashboard/{page}', function ($page) {
        $validPages = ['overview', 'reservations', 'calendar', 'tables', 'customers', 'analytics', 'settings', 'audit'];
        if (! in_array($page, $validPages)) {
            abort(404);
        }

        return Inertia::render('Dashboard/'.ucfirst($page));
    })->name('dashboard.page');
});
