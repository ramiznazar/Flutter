<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MiningController;
use App\Http\Controllers\Api\MiningProxyController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskProxyController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\KycProxyController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\GiveawayController;
use App\Http\Controllers\Api\BoosterController;
use App\Http\Controllers\Api\MysteryBoxController;
use App\Http\Controllers\Api\GamificationProxyController;
use App\Http\Controllers\Api\SpinController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\NewsManageController;
use App\Http\Controllers\Api\Admin\TasksManageController;
use App\Http\Controllers\Api\Admin\ShopManageController;
use App\Http\Controllers\Api\Admin\GiveawayManageController;
use App\Http\Controllers\Api\Admin\SettingsManageController;
use App\Http\Controllers\Api\Admin\UsersManageController;
use App\Http\Controllers\Api\Admin\KycManageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/create_account', [AuthController::class, 'signup']); // Alias
Route::post('/otp_request', [AuthController::class, 'otpRequest']);
Route::post('/otp_request_new', [AuthController::class, 'otpRequestNew']);
Route::post('/verify_otp', [AuthController::class, 'verifyOtp']);
Route::post('/verify_otp_and_set_password', [AuthController::class, 'verifyOtpAndSetPassword']);
Route::post('/change_password', [AuthController::class, 'changePassword']);
Route::post('/reset_password', [AuthController::class, 'resetPassword']);

// User Routes
Route::post('/get_user_stats', [UserController::class, 'getUserStats']);
Route::post('/update_profile', [UserController::class, 'updateProfile']);
Route::post('/edit_profile', [UserController::class, 'editProfile']);
Route::post('/change_pic', [UserController::class, 'changePic']);
Route::post('/get_team', [UserController::class, 'getTeam']);
Route::post('/getLevel', [UserController::class, 'getLevel']);
Route::post('/getBadges', [UserController::class, 'getBadges']);
Route::post('/check_levels', [UserController::class, 'checkLevels']);
Route::post('/update_user_guide', [UserController::class, 'updateUserGuide']);
Route::post('/update_user_ping', [UserController::class, 'updateUserPing']);
Route::post('/setup_username', [UserController::class, 'setupUsername']);
Route::post('/setup_invite', [UserController::class, 'setupInvite']);
Route::post('/delete_account_request', [UserController::class, 'deleteAccountRequest']);
Route::post('/reactivate_account', [UserController::class, 'reactivateAccount']);

// Mining Routes (gateway: when MINING_SERVICE_ENABLED=true, proxy to mining service; else local MiningController)
Route::post('/start_mining', [MiningProxyController::class, 'startMining']);
Route::get('/mining_status', [MiningProxyController::class, 'miningStatus']);
Route::post('/start_coin', [MiningProxyController::class, 'startCoin']);
Route::post('/claim_bonus', [MiningProxyController::class, 'claimBonus']);
Route::post('/bonus_history', [MiningProxyController::class, 'bonusHistory']);
Route::post('/social_claim', [MiningProxyController::class, 'socialClaim']);
Route::post('/social_list', [MiningProxyController::class, 'socialList']);
Route::post('/add_daily_reward', [MiningProxyController::class, 'addDailyReward']);
Route::post('/get_daily_reward_status', [MiningProxyController::class, 'getDailyRewardStatus']);

// Task Routes (gateway: when TASK_SERVICE_ENABLED=true, proxy to task service; else local TaskController)
Route::post('/task_start', [TaskProxyController::class, 'taskStart']);
Route::post('/task_claim_reward', [TaskProxyController::class, 'taskClaimReward']);
Route::post('/task_track', [TaskProxyController::class, 'trackTask']);
Route::post('/get_daily_tasks', [TaskProxyController::class, 'getDailyTasks']);

// Gamification Routes (gateway: when GAMIFICATION_SERVICE_ENABLED=true, proxy to gamification service; else local)
Route::post('/booster_status', [GamificationProxyController::class, 'boosterStatus']);
Route::post('/booster_claim', [GamificationProxyController::class, 'boosterClaim']);
Route::post('/mystery_box_watch_ad', [GamificationProxyController::class, 'mysteryBoxWatchAd']);
Route::post('/mystery_box_click', [GamificationProxyController::class, 'mysteryBoxClick']);
Route::post('/mystery_box_open', [GamificationProxyController::class, 'mysteryBoxOpen']);
Route::post('/mystery_box_details', [GamificationProxyController::class, 'mysteryBoxDetails']);
Route::post('/ad_booster_status', [GamificationProxyController::class, 'adBoosterStatus']);
Route::post('/ad_booster_claim', [GamificationProxyController::class, 'adBoosterClaim']);

// KYC Routes (gateway: when KYC_SERVICE_ENABLED=true, proxy to KYC service; else local KycController)
Route::post('/kyc_check_eligibility', [KycProxyController::class, 'checkEligibility']);
Route::match(['get', 'post'], '/kyc_submit', [KycProxyController::class, 'submit']);
Route::post('/submit_kyc', [KycProxyController::class, 'submit']); // Alias
Route::post('/kyc_get_status', [KycProxyController::class, 'getStatus']);
Route::post('/get_kyc_progress', [KycProxyController::class, 'getProgress']);
Route::post('/didit_create_request', [KycProxyController::class, 'diditCreateRequest']);

// News Routes
Route::post('/get_all_news', [NewsController::class, 'getAllNews']);
Route::post('/get_news', [NewsController::class, 'getNews']);
Route::post('/add_news', [NewsController::class, 'addNews']);
Route::post('/delete_news', [NewsController::class, 'deleteNews']);
Route::post('/like_news', [NewsController::class, 'likeNews']);
Route::post('/set_news_view', [NewsController::class, 'setNewsView']);

// Shop Routes
Route::post('/get_all_shops', [ShopController::class, 'getAllShops']);
Route::post('/set_shop_view', [ShopController::class, 'setShopView']);
Route::post('/giftcard_track', [ShopController::class, 'trackGiftcard']);

// Giveaway Routes
Route::post('/get_giveaway', [GiveawayController::class, 'getGiveaway']);

// Spin Routes
Route::post('/spin', [SpinController::class, 'spin']);
Route::post('/spin_claim', [SpinController::class, 'spinClaim']);
Route::post('/get_myspin_info', [SpinController::class, 'getMySpinInfo']);

// Settings Routes (GET other_settings = same as POST, for app startup / Flutter)
Route::get('/other_settings', [SettingsController::class, 'otherSettings']);
Route::post('/other_settings', [SettingsController::class, 'otherSettings']);
Route::post('/get_currencies', [SettingsController::class, 'getCurrencies']);
Route::get('/getTotalUsers', [SettingsController::class, 'getTotalUsers']);
Route::post('/time', [SettingsController::class, 'time']);
Route::get('/ads', [SettingsController::class, 'ads']);
Route::post('/ads', [SettingsController::class, 'ads']);

// Utility Routes
Route::get('/get_email', [AuthController::class, 'getEmail']);
Route::post('/send_notification', [\App\Http\Controllers\Api\NotificationController::class, 'sendNotification']);

// Admin Authentication
Route::post('/login_admin', [AdminController::class, 'login']);

// Admin Management Routes
Route::prefix('admin')->group(function () {
    Route::get('/news_manage', [NewsManageController::class, 'index']);
    Route::post('/news_manage', [NewsManageController::class, 'store']);
    Route::put('/news_manage/{id}', [NewsManageController::class, 'update']);
    Route::delete('/news_manage/{id}', [NewsManageController::class, 'destroy']);
    
    Route::get('/tasks_manage', [TasksManageController::class, 'index']);
    Route::post('/tasks_manage', [TasksManageController::class, 'store']);
    Route::put('/tasks_manage/{id}', [TasksManageController::class, 'update']);
    Route::delete('/tasks_manage/{id}', [TasksManageController::class, 'destroy']);
    
    Route::get('/shop_manage', [ShopManageController::class, 'index']);
    Route::post('/shop_manage', [ShopManageController::class, 'store']);
    Route::put('/shop_manage/{id}', [ShopManageController::class, 'update']);
    Route::delete('/shop_manage/{id}', [ShopManageController::class, 'destroy']);
    
    Route::get('/giveaway_manage', [GiveawayManageController::class, 'index']);
    Route::post('/giveaway_manage', [GiveawayManageController::class, 'store']);
    Route::put('/giveaway_manage/{id}', [GiveawayManageController::class, 'update']);
    Route::delete('/giveaway_manage/{id}', [GiveawayManageController::class, 'destroy']);
    
    Route::get('/settings_manage', [SettingsManageController::class, 'index']);
    Route::post('/settings_manage', [SettingsManageController::class, 'update']);
    Route::put('/settings_manage', [SettingsManageController::class, 'update']);
    
    Route::get('/users_manage', [UsersManageController::class, 'index']);
    Route::post('/users_manage/give_coins', [UsersManageController::class, 'giveCoins']);
    Route::post('/users_manage/give_booster', [UsersManageController::class, 'giveBooster']);
    Route::get('/users_manage/stats', [UsersManageController::class, 'getUserStats']);
    Route::post('/users_manage/stats', [UsersManageController::class, 'updateUserStats']);
    Route::get('/users_manage/coin_speed', [UsersManageController::class, 'getUserCoinSpeed']);
    Route::post('/users_manage/coin_speed', [UsersManageController::class, 'updateUserCoinSpeed']);
    
    Route::get('/coin_speed_overall', [SettingsManageController::class, 'getCoinSpeedOverall']);
    Route::post('/coin_speed_overall', [SettingsManageController::class, 'updateCoinSpeedOverall']);
    
    Route::get('/kyc_manage', [KycManageController::class, 'index']);
    Route::put('/kyc_manage/{id}', [KycManageController::class, 'update']);
    
    Route::post('/mystery_box_reset', [AdminController::class, 'mysteryBoxReset']);
    Route::post('/user_stats_manage', [AdminController::class, 'userStatsManage']);
});
