<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        // Role test route (Phase 2.2 demonstration)
        Route::get('/role-test', function () {
            return response()->json(['message' => 'Access granted.']);
        })->middleware('role:owner');
    });
});
