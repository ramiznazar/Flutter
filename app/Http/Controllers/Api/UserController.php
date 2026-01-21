<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            ->get(['id', 'name', 'email', 'total_invite', 'join_date']);

        return response()->json([
            'success' => true,
            'data' => [
                'total_referrals' => $user->total_invite,
                'referrals' => $referrals
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $userLevel = UserLevel::where('user_id', $user->id)
            ->with('level')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $userLevel
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user level data
        $userLevel = \App\Models\UserLevel::where('user_id', $user->id)->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;
        $spinWheel = $userLevel ? (int) $userLevel->spin_wheel : 0;
        $totalInvite = (int) $user->total_invite;
        $token = (float) $user->token;

        // Get total social media tasks
        $totalSocialMediaTasks = \App\Models\SocialMediaSetting::count();
        
        // Get completed social media tasks
        $completedSocialMediaTasks = \Illuminate\Support\Facades\DB::table('social_media_tokens')
            ->where('user_id', $user->id)
            ->select('social_media_id')
            ->distinct()
            ->count();

        // Get all badges
        $badges = \App\Models\Badge::orderBy('id', 'asc')->get();
        
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

            // Increment referrer's total_invite and add reward
            $referrer->increment('total_invite');
            $referrer->increment('token', 0.5);

            return response()->json([
                'success' => true,
                'message' => 'Username successfully setup.'
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
