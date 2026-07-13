<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ReservationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth endpoints
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        // Role test route (Phase 2.2 demonstration)
        Route::get('/role-test', function () {
            return response()->json(['message' => 'Access granted.']);
        })->middleware('role:owner');

        // Reservation routes (all require authentication)
        Route::get('/reservations', [ReservationController::class, 'index'])->name('api.v1.reservations.index');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('api.v1.reservations.store');
        Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('api.v1.reservations.show');
        Route::patch('/reservations/{id}', [ReservationController::class, 'update'])->name('api.v1.reservations.update');
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])
            ->middleware('role:manager')
            ->name('api.v1.reservations.destroy');
        Route::post('/reservations/bulk', [ReservationController::class, 'bulk'])->name('api.v1.reservations.bulk');
    });
});