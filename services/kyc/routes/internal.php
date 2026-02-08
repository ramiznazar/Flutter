<?php

use App\Http\Controllers\InternalKycController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes (Gateway → KYC Service)
|--------------------------------------------------------------------------
| Prefix: /internal. Protected by INTERNAL_API_SECRET.
| Contract: see monolith docs/INTERNAL_API_CONTRACT.md (KYC endpoints).
|
*/

Route::middleware(\App\Http\Middleware\VerifyInternalSecret::class)->group(function () {
    Route::post('/kyc_check_eligibility', [InternalKycController::class, 'checkEligibility']);
    Route::post('/kyc_submit', [InternalKycController::class, 'submit']);
    Route::post('/submit_kyc', [InternalKycController::class, 'submit']);
    Route::post('/kyc_get_status', [InternalKycController::class, 'getStatus']);
    Route::post('/get_kyc_progress', [InternalKycController::class, 'getProgress']);
    Route::post('/didit_create_request', [InternalKycController::class, 'diditCreateRequest']);
});
