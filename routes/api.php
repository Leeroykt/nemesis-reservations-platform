<?php

// No routes needed here – all are moved to web.php
// We keep the file for future public API endpoints.
use App\Http\Controllers\Api\V1\PublicReservationController;

Route::post('/public/reservations', [PublicReservationController::class, 'store'])
    ->middleware('throttle:5,1');
