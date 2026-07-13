<?php

use App\Http\Controllers\Api\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthTokenController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::post('/refresh', [AuthTokenController::class, 'refresh'])
        ->middleware('throttle:30,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthTokenController::class, 'me']);
        Route::post('/logout', [AuthTokenController::class, 'logout']);
        Route::post('/logout-all', [AuthTokenController::class, 'logoutAll']);
    });
});
