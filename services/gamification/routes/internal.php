<?php

use App\Http\Controllers\InternalGamificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes (Gateway → Gamification Service)
|--------------------------------------------------------------------------
| Prefix: /internal. Protected by INTERNAL_API_SECRET.
| Contract: see monolith docs/INTERNAL_API_CONTRACT.md (Gamification endpoints).
|
*/

Route::middleware(\App\Http\Middleware\VerifyInternalSecret::class)->group(function () {
    Route::post('/mystery_box_watch_ad', [InternalGamificationController::class, 'mysteryBoxWatchAd']);
    Route::post('/mystery_box_click', [InternalGamificationController::class, 'mysteryBoxClick']);
    Route::post('/mystery_box_open', [InternalGamificationController::class, 'mysteryBoxOpen']);
    Route::post('/mystery_box_details', [InternalGamificationController::class, 'mysteryBoxDetails']);
    Route::post('/booster_status', [InternalGamificationController::class, 'boosterStatus']);
    Route::post('/booster_claim', [InternalGamificationController::class, 'boosterClaim']);
    Route::post('/ad_booster_status', [InternalGamificationController::class, 'adBoosterStatus']);
    Route::post('/ad_booster_claim', [InternalGamificationController::class, 'adBoosterClaim']);
});
