<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminViewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', [AdminViewController::class, 'dashboard'])->name('dashboard');
        Route::post('/dashboard/update', [AdminViewController::class, 'updateUserCount'])->name('dashboard.update');
        
        // News Management
        Route::get('/news', [\App\Http\Controllers\Admin\NewsViewController::class, 'index'])->name('news.index');
        Route::post('/news', [\App\Http\Controllers\Admin\NewsViewController::class, 'store'])->name('news.store');
        Route::post('/news/delete', [\App\Http\Controllers\Admin\NewsViewController::class, 'destroy'])->name('news.destroy');
        // Tasks Management
        Route::get('/tasks', [\App\Http\Controllers\Admin\TasksViewController::class, 'index'])->name('tasks.index');
        Route::post('/tasks/daily', [\App\Http\Controllers\Admin\TasksViewController::class, 'storeDaily'])->name('tasks.store-daily');
        Route::post('/tasks/onetime', [\App\Http\Controllers\Admin\TasksViewController::class, 'storeOnetime'])->name('tasks.store-onetime');
        Route::post('/tasks/update-onetime', [\App\Http\Controllers\Admin\TasksViewController::class, 'updateOnetime'])->name('tasks.update-onetime');
        Route::post('/tasks/delete', [\App\Http\Controllers\Admin\TasksViewController::class, 'destroy'])->name('tasks.destroy');
        
        // Shop Management
        Route::get('/shop', [\App\Http\Controllers\Admin\ShopViewController::class, 'index'])->name('shop.index');
        Route::post('/shop', [\App\Http\Controllers\Admin\ShopViewController::class, 'store'])->name('shop.store');
        Route::post('/shop/delete', [\App\Http\Controllers\Admin\ShopViewController::class, 'destroy'])->name('shop.destroy');
        
        // Giveaway Management
        Route::get('/giveaway', [\App\Http\Controllers\Admin\GiveawayViewController::class, 'index'])->name('giveaway.index');
        Route::post('/giveaway', [\App\Http\Controllers\Admin\GiveawayViewController::class, 'store'])->name('giveaway.store');
        Route::post('/giveaway/delete', [\App\Http\Controllers\Admin\GiveawayViewController::class, 'destroy'])->name('giveaway.destroy');
        // Settings
        Route::get('/mining-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'miningSettings'])->name('mining-settings');
        Route::post('/mining-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'updateMiningSettings'])->name('mining-settings.update');
        Route::post('/mining-settings/user-coin-speed', [\App\Http\Controllers\Admin\SettingsViewController::class, 'updateUserCoinSpeed'])->name('mining-settings.user-coin-speed');
        Route::get('/referral-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'referralSettings'])->name('referral-settings');
        Route::post('/referral-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'updateReferralSettings'])->name('referral-settings.update');
        Route::get('/mystery-box', [\App\Http\Controllers\Admin\SettingsViewController::class, 'mysteryBoxSettings'])->name('mystery-box');
        Route::post('/mystery-box', [\App\Http\Controllers\Admin\SettingsViewController::class, 'updateMysteryBoxSettings'])->name('mystery-box.update');
        Route::get('/kyc-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'kycSettings'])->name('kyc-settings');
        Route::post('/kyc-settings', [\App\Http\Controllers\Admin\SettingsViewController::class, 'updateKycSettings'])->name('kyc-settings.update');
        
        // Users Management
        Route::get('/users', [\App\Http\Controllers\Admin\UsersViewController::class, 'index'])->name('users.index');
        Route::post('/users/give-coins', [\App\Http\Controllers\Admin\UsersViewController::class, 'giveCoins'])->name('users.give-coins');
        Route::post('/users/give-booster', [\App\Http\Controllers\Admin\UsersViewController::class, 'giveBooster'])->name('users.give-booster');
        Route::post('/users/reset-mystery-box', [\App\Http\Controllers\Admin\UsersViewController::class, 'resetMysteryBox'])->name('users.reset-mystery-box');
        
        // KYC Management
        Route::get('/kyc', [\App\Http\Controllers\Admin\KycViewController::class, 'index'])->name('kyc.index');
        Route::post('/kyc/update-status', [\App\Http\Controllers\Admin\KycViewController::class, 'updateStatus'])->name('kyc.update-status');
        Route::get('/user-stats', function() { return view('admin.user-stats'); })->name('user-stats');
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    });
});
