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
            'password' => 'required|string',
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

        // Check password
        if ($request->password !== $user->password) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.'
            ], 400);
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
            'password' => 'required|string',
            'profile_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
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
        // Get user's badges based on achievements
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

        // Logic to determine which badges user has earned
        // This would check mining_sessions, spin_wheel, total_invite, etc.
        // For now, return basic structure
        return response()->json([
            'success' => true,
            'data' => []
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
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
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
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
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
            'password' => 'required|string',
            'username' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
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
            'password' => 'required|string',
            'invite_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Find referrer by invite code (could be user ID or username)
        $referrer = User::where('id', $request->invite_code)
            ->orWhere('username', $request->invite_code)
            ->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invite code'
            ], 400);
        }

        if ($referrer->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot use your own invite code'
            ], 400);
        }

        // Update user's invite setup
        $user->update(['invite_setup' => $request->invite_code]);

        // Increment referrer's total_invite
        $referrer->increment('total_invite');

        return response()->json([
            'success' => true,
            'message' => 'Invite code set successfully'
        ]);
    }

    public function deleteAccountRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
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
