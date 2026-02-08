<?php

use App\Http\Controllers\InternalTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes (Gateway → Task Service)
|--------------------------------------------------------------------------
| Prefix: /internal. Protected by INTERNAL_API_SECRET.
| Contract: see monolith docs/INTERNAL_API_CONTRACT.md (Task endpoints).
|
*/

Route::middleware(\App\Http\Middleware\VerifyInternalSecret::class)->group(function () {
    Route::post('/task_start', [InternalTaskController::class, 'taskStart']);
    Route::post('/task_claim_reward', [InternalTaskController::class, 'taskClaimReward']);
    Route::post('/task_track', [InternalTaskController::class, 'trackTask']);
    Route::post('/get_daily_tasks', [InternalTaskController::class, 'getDailyTasks']);
});
