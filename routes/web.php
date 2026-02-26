<?php

use App\Http\Controllers\Admin\DealerController;
use App\Http\Controllers\Admin\DeliveryOrderController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\LeaderboardUploadController;
use App\Http\Controllers\Admin\MonthlyResetController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\TvDisplayController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('leaderboard.index');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'create'])->name('login');
    Route::post('/login', [WebAuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [WebAuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::get('/leaderboard/data', [LeaderboardController::class, 'data'])->name('leaderboard.data');
Route::get('/leaderboard/tv', [TvDisplayController::class, 'index'])->name('leaderboard.tv');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resources([
        'dealers' => DealerController::class,
        'sales' => SalesController::class,
        'penjualans' => PenjualanController::class,
        'delivery-orders' => DeliveryOrderController::class,
    ]);

    Route::get('/export/leaderboard', [ExportController::class, 'leaderboard'])->name('export.leaderboard');
    Route::post('/monthly-reset', [MonthlyResetController::class, 'store'])->name('monthly-reset.store');
    Route::get('/leaderboard-upload', [LeaderboardUploadController::class, 'index'])->name('leaderboard-upload.index');
    Route::post('/leaderboard-upload', [LeaderboardUploadController::class, 'store'])->name('leaderboard-upload.store');
});
