<?php

use App\Http\Controllers\Api\V1\PublicReservationController;
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

// Public booking endpoint (rate-limited) – accessible at /api/v1/public/reservations
Route::post('/v1/public/reservations', [PublicReservationController::class, 'store'])
    ->middleware('throttle:5,1');
