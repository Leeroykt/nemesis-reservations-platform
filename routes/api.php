<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1
| Authentication is handled via Laravel Sanctum
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication)
    |--------------------------------------------------------------------------
    */

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes (Sanctum)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        /*
        |--------------------------------------------------------------------------
        | Protected Routes - Will be added as controllers are created
        |--------------------------------------------------------------------------
        |
        | All routes below are commented out until their controllers exist.
        | They will be uncommented during their respective phases.
        |
        | Phase 3.2: Reservations
        | Phase 3.3: Tables, Customers, Notifications, Dashboard
        | Phase 5.1: Public Booking
        | Phase 6.1: Settings
        | Phase 6.2: Users
        | Phase 6.3: Audit Log
        |
        */

        // Reservations (Phase 3.2)
        // Route::get('/reservations', [ReservationController::class, 'index']);
        // Route::post('/reservations', [ReservationController::class, 'store']);
        // Route::get('/reservations/{id}', [ReservationController::class, 'show']);
        // Route::patch('/reservations/{id}', [ReservationController::class, 'update']);
        // Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

        // Tables (Phase 3.3)
        // Route::get('/tables', [TableController::class, 'index']);
        // Route::get('/tables/{id}', [TableController::class, 'show']);
        // Route::patch('/tables/{id}/status', [TableController::class, 'updateStatus']);

        // Customers (Phase 3.3)
        // Route::get('/customers', [CustomerController::class, 'index']);
        // Route::get('/customers/{id}', [CustomerController::class, 'show']);

        // Notifications (Phase 3.3)
        // Route::get('/notifications', [NotificationController::class, 'index']);
        // Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

        // Dashboard (Phase 3.3)
        // Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
        // Route::get('/dashboard/revenue-trend', [DashboardController::class, 'revenueTrend']);
        // Route::get('/dashboard/status-breakdown', [DashboardController::class, 'statusBreakdown']);
        // Route::get('/activity', [DashboardController::class, 'activity']);

        // Analytics (Phase 3.3)
        // Route::get('/analytics/peak-hours', [AnalyticsController::class, 'peakHours']);
        // Route::get('/analytics/popular-tables', [AnalyticsController::class, 'popularTables']);
        // Route::get('/analytics/customer-growth', [AnalyticsController::class, 'customerGrowth']);

        // Settings (Phase 6.1)
        // Route::get('/settings/restaurant', [SettingsController::class, 'getRestaurant']);
        // Route::patch('/settings/restaurant', [SettingsController::class, 'updateRestaurant']);
        // Route::get('/settings/hours', [SettingsController::class, 'getHours']);
        // Route::patch('/settings/hours', [SettingsController::class, 'updateHours']);
        // Route::get('/settings/rules', [SettingsController::class, 'getRules']);
        // Route::patch('/settings/rules', [SettingsController::class, 'updateRules']);

        // Users (Phase 6.2)
        // Route::get('/users', [UserController::class, 'index']);
        // Route::post('/users', [UserController::class, 'store']);
        // Route::patch('/users/{id}', [UserController::class, 'update']);
        // Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Audit Log (Phase 6.3)
        // Route::get('/audit-log', [AuditLogController::class, 'index']);

        // Public Booking (Phase 5.1) - No auth required, but rate-limited
        // Route::post('/public/reservations', [PublicReservationController::class, 'store'])
        //     ->middleware('throttle:5,1');
    });
});