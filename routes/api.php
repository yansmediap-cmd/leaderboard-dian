<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\SpkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api-login', 'api.activity'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'api.activity'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['throttle:api-read', 'role:admin,viewer,api'])->group(function () {
        Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    });

    Route::middleware(['throttle:api-ingest', 'role:admin,api', 'dealer.ip'])->group(function () {
        Route::post('/spk/store', [SpkController::class, 'store']);
        Route::post('/do/store', [DoController::class, 'store']);
    });
});
