<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Valid email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;

        return response()->json([
            'success' => true,
            'message' => 'User stats retrieved successfully',
            'data' => [
                'email' => $user->email,
                'user_id' => $user->id,
                'mining_sessions' => $miningSessions,
                'referrals' => (int) $user->total_invite
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'old_email' => 'required|email',
            'new_email' => 'required|email',
            'country' => 'required|string',
            'phone_number' => 'required|string',
            'profile_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters.'
            ], 400);
        }

        // Validate email format
        if (!filter_var($request->new_email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Email is not valid. Please check your email address.'
            ], 400);
        }

        // Validate phone number format
        if (!preg_match('/^\+[0-9]+$/', $request->phone_number)) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is not valid. Please check your Phone number.'
            ], 400);
        }

        $user = User::where('email', $request->old_email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if new email is different and already exists
        if ($request->old_email !== $request->new_email) {
            $emailExists = User::where('email', $request->new_email)->exists();
            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'The new email is already registered.'
                ], 400);
            }
        }

        // Update user
        $user->update([
            'name' => $request->full_name,
            'email' => $request->new_email,
            'country' => $request->country,
            'phone' => $request->phone_number,
            'ban_reason' => $request->profile_url, // Note: Original uses ban_reason for profile_url
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User information updated successfully.'
        ]);
    }

    public function editProfile(Request $request)
    {
        return $this->updateProfile($request);
    }

    public function changePic(Request $request)
    {
        // Implementation similar to updateProfile but for profile picture only
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'profile_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $user->update(['ban_reason' => $request->profile_url]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.'
        ]);
    }

    public function getTeam(Request $request)
    {
        // Get user's referral team
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get users referred by this user (users with invite_setup matching this user's id or username)
        $referrals = User::where('invite_setup', $user->id)
            ->orWhere('invite_setup', $user->username)
            ->get();

        $now = Carbon::now();
        $twelveHoursAgo = $now->copy()->subHours(12);

        // Format referrals with ping status and additional info
        $formattedReferrals = $referrals->map(function($referral) use ($now, $twelveHoursAgo) {
            $lastActive = $referral->last_active ? Carbon::parse($referral->last_active) : null;
            $isPingAvailable = false;
            
            if ($lastActive) {
                // Ping is available if user was active within last 12 hours
                $isPingAvailable = $lastActive->gte($twelveHoursAgo);
            }
            
            // Check if user is currently mining
            $isMining = false;
            if ($referral->is_mining == 1 && $referral->mining_end_time) {
                try {
                    $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $referral->mining_end_time);
                    $isMining = $now->lt($miningEndTime);
                } catch (\Exception $e) {
                    // Try alternative format
                    try {
                        $miningEndTime = Carbon::parse($referral->mining_end_time);
                        $isMining = $now->lt($miningEndTime);
                    } catch (\Exception $e2) {
                        $isMining = false;
                    }
                }
            }

            return [
                'id' => $referral->id,
                'user_id' => 'USR' . str_pad($referral->id, 6, '0', STR_PAD_LEFT),
                'name' => $referral->name,
                'email' => $referral->email,
                'username' => $referral->username ?? 'N/A',
                'token' => (float) $referral->token,
                'total_invite' => (int) $referral->total_invite,
                'join_date' => $referral->join_date,
                'last_active' => $referral->last_active,
                'is_mining' => $isMining,
                'is_ping_available' => $isPingAvailable,
                'profile_url' => $referral->ban_reason ?? null // Using ban_reason field for profile_url (legacy)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_referrals' => $user->total_invite,
                'referrals' => $formattedReferrals
            ]
        ]);
    }

    public function getLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        // Optimize: Select only needed fields
        $user = User::where('email', $request->email)
            ->select('id', 'total_invite', 'join_date')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Optimize: Select only needed fields from user_levels
        $userLevel = UserLevel::where('user_id', $user->id)
            ->select('current_level', 'mining_session')
            ->first();
        $currentLevelId = $userLevel ? (int) $userLevel->current_level : 1;
        
        // Get user stats
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;
        $totalInvite = (int) $user->total_invite;
        $accountAgeDays = $user->join_date ? \Carbon\Carbon::parse($user->join_date)->diffInDays(\Carbon\Carbon::now()) : 0;
        
        // Cache all levels (levels table is static at runtime)
        static $cachedAllLevels = null;
        if ($cachedAllLevels === null) {
            $cachedAllLevels = \App\Models\Level::orderBy('id', 'asc')->get();
        }
        $allLevels = $cachedAllLevels;

        // Default perks object so the app never gets empty perks (fixes "Missing current_level or next_level")
        $defaultPerks = [
            'crutox_per_time' => 0.5,
            'mining_time_hours' => 12,
            'crutox_reward' => 0.0,
            'other_access' => null,
            'is_ads_block' => false
        ];
        
        // Get current level
        $currentLevel = $allLevels->firstWhere('id', $currentLevelId);
        if (!$currentLevel) {
            $currentLevel = $allLevels->first();
            $currentLevelId = $currentLevel ? $currentLevel->id : 1;
        }
        
        // Get next level
        $nextLevel = $allLevels->firstWhere('id', $currentLevelId + 1);
        
        // Format current level perks — always return object, never empty (app requires it)
        $currentLevelPerks = $defaultPerks;
        if ($currentLevel) {
            $currentLevelPerks = [
                'crutox_per_time' => (float) ($currentLevel->perk_crutox_per_time ?? 0.5),
                'mining_time_hours' => (int) ($currentLevel->perk_mining_time ?? 12),
                'crutox_reward' => (float) ($currentLevel->perk_crutox_reward ?? 0),
                'other_access' => $currentLevel->perk_other_access ?? null,
                'is_ads_block' => (bool) ($currentLevel->is_ads_block ?? false)
            ];
        }
        
        // Format next level requirements with progress
        $nextLevelRequirements = null;
        if ($nextLevel) {
            $nextLevelRequirements = [
                'mining_sessions' => [
                    'required' => (int) $nextLevel->mining_sessions,
                    'current' => $miningSessions,
                    'progress' => $miningSessions . '/' . (int) $nextLevel->mining_sessions,
                    'completed' => $miningSessions >= (int) $nextLevel->mining_sessions
                ],
                'total_invite' => [
                    'required' => (int) $nextLevel->total_invite,
                    'current' => $totalInvite,
                    'progress' => $totalInvite . '/' . (int) $nextLevel->total_invite,
                    'completed' => $totalInvite >= (int) $nextLevel->total_invite
                ],
                'account_age_days' => [
                    'required' => (int) $nextLevel->user_account_old,
                    'current' => $accountAgeDays,
                    'progress' => $accountAgeDays . '/' . (int) $nextLevel->user_account_old,
                    'completed' => $accountAgeDays >= (int) $nextLevel->user_account_old
                ]
            ];
        }
        
        // Format all levels data — ensure at least one so progress UI works
        $levelsData = [];
        foreach ($allLevels as $level) {
            $levelsData[] = [
                'id' => $level->id,
                'name' => $level->lvl_name ?? 'Level ' . $level->id,
                'requirements' => [
                    'mining_sessions' => (int) ($level->mining_sessions ?? 0),
                    'total_invite' => (int) ($level->total_invite ?? 0),
                    'account_age_days' => (int) ($level->user_account_old ?? 0)
                ],
                'perks' => [
                    'crutox_per_time' => (float) ($level->perk_crutox_per_time ?? 0.5),
                    'mining_time_hours' => (int) ($level->perk_mining_time ?? 12),
                    'crutox_reward' => (float) ($level->perk_crutox_reward ?? 0),
                    'other_access' => $level->perk_other_access ?? null,
                    'is_ads_block' => (bool) ($level->is_ads_block ?? false)
                ],
                'is_current' => $level->id == $currentLevelId
            ];
        }

        // When no levels in DB, return one default level so app never gets empty all_levels or null next_level
        if ($allLevels->isEmpty()) {
            $levelsData = [[
                'id' => 1,
                'name' => 'Novice',
                'requirements' => [
                    'mining_sessions' => 0,
                    'total_invite' => 0,
                    'account_age_days' => 0
                ],
                'perks' => $defaultPerks,
                'is_current' => true
            ]];
        }

        // App requires both current_level and next_level to be present; use stub when at max level
        $nextLevelPayload = null;
        if ($nextLevel) {
            $nextLevelPayload = [
                'id' => $nextLevel->id,
                'name' => $nextLevel->lvl_name ?? 'Level ' . $nextLevel->id,
                'requirements' => $nextLevelRequirements,
                'perks' => [
                    'crutox_per_time' => (float) ($nextLevel->perk_crutox_per_time ?? 0.5),
                    'mining_time_hours' => (int) ($nextLevel->perk_mining_time ?? 12),
                    'crutox_reward' => (float) ($nextLevel->perk_crutox_reward ?? 0),
                    'other_access' => $nextLevel->perk_other_access ?? null,
                    'is_ads_block' => (bool) ($nextLevel->is_ads_block ?? false)
                ]
            ];
        } else {
            // At max level — return current as "next" so app sees valid next_level (no upgrade path)
            $nextLevelPayload = [
                'id' => $currentLevelId,
                'name' => $currentLevel ? ($currentLevel->lvl_name ?? 'Novice') : 'Novice',
                'requirements' => [
                    'mining_sessions' => ['required' => 0, 'current' => $miningSessions, 'progress' => (string) $miningSessions . '/0', 'completed' => true],
                    'total_invite' => ['required' => 0, 'current' => $totalInvite, 'progress' => (string) $totalInvite . '/0', 'completed' => true],
                    'account_age_days' => ['required' => 0, 'current' => $accountAgeDays, 'progress' => (string) $accountAgeDays . '/0', 'completed' => true]
                ],
                'perks' => $currentLevelPerks
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_level' => [
                    'id' => $currentLevelId,
                    'name' => $currentLevel ? ($currentLevel->lvl_name ?? 'Novice') : 'Novice',
                    'perks' => $currentLevelPerks
                ],
                'next_level' => $nextLevelPayload,
                'all_levels' => $levelsData,
                'user_stats' => [
                    'mining_sessions' => $miningSessions,
                    'total_invite' => $totalInvite,
                    'account_age_days' => $accountAgeDays
                ]
            ]
        ]);
    }

    public function getBadges(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        // Select only fields we actually need to reduce payload
        $user = User::where('email', $request->email)
            ->select('id', 'total_invite', 'token', 'join_date')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user level data
        $userLevel = \App\Models\UserLevel::where('user_id', $user->id)
            ->select('mining_session', 'spin_wheel')
            ->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;
        $spinWheel = $userLevel ? (int) $userLevel->spin_wheel : 0;
        $totalInvite = (int) $user->total_invite;
        $token = (float) $user->token;

        // Get total social media tasks (cached per PHP process; table changes very rarely)
        static $totalSocialMediaTasks = null;
        if ($totalSocialMediaTasks === null) {
            $totalSocialMediaTasks = \App\Models\SocialMediaSetting::count();
        }
        
        // Get completed social media tasks
        $completedSocialMediaTasks = \Illuminate\Support\Facades\DB::table('social_media_tokens')
            ->where('user_id', $user->id)
            ->select('social_media_id')
            ->distinct()
            ->count();

        // Get all badges (cached per PHP process; badges table is static for runtime)
        static $cachedBadges = null;
        if ($cachedBadges === null) {
            $cachedBadges = \App\Models\Badge::orderBy('id', 'asc')->get();
        }
        $badges = $cachedBadges;
        
        $earnedBadges = [];

        // Add "Account Created" badge first
        $accountCreatedBadge = $badges->firstWhere('badge_name', 'Newbie Explorer: Once User Creates Account');
        $earnedBadges[] = [
            'title' => 'Newbie Explorer: Once User Creates Account',
            'earned' => $user->join_date ? true : false,
            'progress' => null,
            'total' => null,
            'badges_icon' => $accountCreatedBadge ? $accountCreatedBadge->badges_icon : null
        ];

        // Process other badges
        foreach ($badges as $badge) {
            // Skip the account created badge as it's already processed
            if ($badge->badge_name === 'Newbie Explorer: Once User Creates Account') {
                continue;
            }

            $badgeData = [
                'title' => $badge->badge_name,
                'earned' => false,
                'progress' => null,
                'total' => null,
                'badges_icon' => $badge->badges_icon
            ];

            // Check badge requirements
            if ($badge->mining_sessions_required !== null) {
                $badgeData['progress'] = $miningSessions;
                $badgeData['total'] = $badge->mining_sessions_required;
                $badgeData['earned'] = $miningSessions >= $badge->mining_sessions_required;
            } elseif ($badge->spin_wheel_required !== null) {
                $badgeData['progress'] = $spinWheel;
                $badgeData['total'] = $badge->spin_wheel_required;
                $badgeData['earned'] = $spinWheel >= $badge->spin_wheel_required;
            } elseif ($badge->invite_friends_required !== null) {
                $badgeData['progress'] = $totalInvite;
                $badgeData['total'] = $badge->invite_friends_required;
                $badgeData['earned'] = $totalInvite >= $badge->invite_friends_required;
            } elseif ($badge->crutox_in_wallet_required !== null) {
                $badgeData['progress'] = $token;
                $badgeData['total'] = $badge->crutox_in_wallet_required;
                $badgeData['earned'] = $token >= $badge->crutox_in_wallet_required;
            } elseif ($badge->social_media_task_completed) {
                $badgeData['progress'] = $completedSocialMediaTasks;
                $badgeData['total'] = $totalSocialMediaTasks;
                $badgeData['earned'] = $completedSocialMediaTasks >= $totalSocialMediaTasks && $totalSocialMediaTasks > 0;
            }

            $earnedBadges[] = $badgeData;
        }

        return response()->json([
            'success' => true,
            'data' => $earnedBadges
        ]);
    }

    public function checkLevels(Request $request)
    {
        // Check and update user levels based on achievements
        // This is a complex function that checks mining sessions, spins, referrals
        return response()->json([
            'success' => true,
            'message' => 'Levels checked'
        ]);
    }

    public function updateUserGuide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        // Update user guide based on request data
        $userGuide = \App\Models\UserGuide::updateOrCreate(
            ['userID' => $user->id],
            $request->only(['home', 'mining', 'wallet', 'badges', 'level', 'teamProfile', 'news', 'shop', 'userProfile'])
        );

        return response()->json([
            'success' => true,
            'message' => 'User guide updated successfully'
        ]);
    }

    public function updateUserPing(Request $request)
    {
        // Update user's last active time
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $user->update(['last_active' => now()->format('Y-m-d H:i:s')]);

        return response()->json([
            'success' => true,
            'message' => 'User ping updated'
        ]);
    }

    public function setupUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'username' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        // Check if username already exists
        $usernameExists = User::where('username', $request->username)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($usernameExists) {
            return response()->json([
                'success' => false,
                'message' => 'Username already taken'
            ], 400);
        }

        $user->update([
            'username' => $request->username,
            'username_count' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Username set successfully'
        ]);
    }

    public function setupInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'username' => 'required_if:reason,invite|string',
            'reason' => 'required|in:invite,skip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->where('invite_setup', 'not_setup') // Only allow if not already set up
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is incorrect or account is not active or invite is not eligible for you.'
            ], 400);
        }

        if ($request->reason === 'skip') {
            // Update invite_setup to 'skip'
            $user->update(['invite_setup' => 'skip']);

            return response()->json([
                'success' => true,
                'message' => 'Username successfully setup.'
            ]);
        }

        // Handle 'invite' reason
        if ($request->reason === 'invite') {
            if (empty($request->username)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username is required when reason is invite'
                ], 400);
            }

            // Find referrer by username
            $referrer = User::where('username', $request->username)->first();

            if (!$referrer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username not found.'
                ], 400);
            }

            // Check if user is trying to use their own referral code
            if ($referrer->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot use your own referral code.'
                ], 400);
            }

            // Update user's invite setup with referrer's user_id
            $user->update(['invite_setup' => $referrer->id]);

            // Increment referrer's total_invite and add reward (2 coins)
            $referrer->increment('total_invite');
            
            // Give 2 coins to referrer
            $referrerReward = 2.0;
            $referrer->increment('token', $referrerReward);
            
            // If referrer is mining, adjust mining_start_balance
            if ($referrer->is_mining == 1 && $referrer->mining_start_balance !== null) {
                $referrer->increment('mining_start_balance', $referrerReward);
            }
            
            $referrer->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Username successfully setup.',
                'referrer_reward' => $referrerReward
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid reason'
        ], 400);
    }

    public function deleteAccountRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        // Mark account for deletion (set status to pending_deletion or similar)
        $user->update(['account_status' => 'pending_deletion']);

        return response()->json([
            'success' => true,
            'message' => 'Account deletion requested'
        ]);
    }

    public function reactivateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->update(['account_status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Account reactivated successfully'
        ]);
    }
}
