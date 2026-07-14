<?php

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\PublicReservationController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================
// PUBLIC ROUTES (No Authentication)
// ============================================================

// Public booking endpoint (rate-limited)
Route::post('/v1/public/reservations', [PublicReservationController::class, 'store'])
    ->middleware('throttle:5,1');

// ============================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // ============================================================
    // OWNER-ONLY ROUTES
    // ============================================================
    Route::middleware(['role:owner'])->group(function () {

        // Restaurant Settings
        Route::get('/settings/restaurant', [SettingsController::class, 'getRestaurant']);
        Route::patch('/settings/restaurant', [SettingsController::class, 'updateRestaurant']);

        // Opening Hours
        Route::get('/settings/hours', [SettingsController::class, 'getHours']);
        Route::patch('/settings/hours', [SettingsController::class, 'updateHours']);

        // Booking Rules
        Route::get('/settings/rules', [SettingsController::class, 'getRules']);
        Route::patch('/settings/rules', [SettingsController::class, 'updateRules']);

        // Branding
        Route::patch('/settings/branding', [SettingsController::class, 'updateBranding']);

        // User Management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // Email Templates
        Route::get('/settings/email-templates', [EmailTemplateController::class, 'index']);
        Route::get('/settings/email-templates/{key}', [EmailTemplateController::class, 'show']);
        Route::patch('/settings/email-templates/{key}', [EmailTemplateController::class, 'update']);

        // Audit Log
        Route::get('/audit-log', [AuditLogController::class, 'index']);
        Route::get('/audit-log/entity-types', [AuditLogController::class, 'entityTypes']);
    });
});