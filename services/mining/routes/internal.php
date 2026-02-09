<?php

use App\Http\Controllers\InternalMiningController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes (Gateway → Mining Service)
|--------------------------------------------------------------------------
| Prefix: /internal. Protected by INTERNAL_API_SECRET.
| Contract: see monolith docs/INTERNAL_API_CONTRACT.md
|
*/

Route::middleware('internal.secret')->group(function () {
    Route::post('/user_mining_stats', [InternalMiningController::class, 'userMiningStats']);
    Route::post('/start_mining', [InternalMiningController::class, 'startMining']);
    Route::get('/mining_status', [InternalMiningController::class, 'miningStatus']);
    Route::post('/start_coin', [InternalMiningController::class, 'startCoin']);
    Route::post('/claim_bonus', [InternalMiningController::class, 'claimBonus']);
    Route::post('/bonus_history', [InternalMiningController::class, 'bonusHistory']);
    Route::post('/social_claim', [InternalMiningController::class, 'socialClaim']);
    Route::post('/social_list', [InternalMiningController::class, 'socialList']);
    Route::post('/get_daily_reward_status', [InternalMiningController::class, 'getDailyRewardStatus']);
    Route::post('/add_daily_reward', [InternalMiningController::class, 'addDailyReward']);
});
