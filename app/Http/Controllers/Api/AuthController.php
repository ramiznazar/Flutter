<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Please provide email and password.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->password) // Note: Original uses plain password
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            $emailExists = User::where('email', $request->email)->exists();
            
            return response()->json([
                'status' => 401,
                'message' => $emailExists ? 'Invalid password.' : 'Invalid email or password.'
            ], 401);
        }

        // Generate authentication token
        $authToken = Str::random(64);
        $user->update(['auth_token' => $authToken]);

        // Get user guide
        $userGuide = UserGuide::where('userID', $user->id)->first();
        
        if (!$userGuide) {
            $userGuide = [
                'home' => true,
                'mining' => true,
                'wallet' => true,
                'badges' => true,
                'level' => true,
                'teamProfile' => true,
                'news' => true,
                'shop' => true,
                'userProfile' => true
            ];
        } else {
            $userGuide = [
                'home' => (bool) $userGuide->home,
                'mining' => (bool) $userGuide->mining,
                'wallet' => (bool) $userGuide->wallet,
                'badges' => (bool) $userGuide->badges,
                'level' => (bool) $userGuide->level,
                'teamProfile' => (bool) $userGuide->teamProfile,
                'news' => (bool) $userGuide->news,
                'shop' => (bool) $userGuide->shop,
                'userProfile' => (bool) $userGuide->userProfile
            ];
        }

        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'phone' => $user->phone,
            'country' => $user->country,
            'token' => $user->token,
            'coin' => $user->coin,
            'is_mining' => $user->is_mining,
            'mining_end_time' => $user->mining_end_time,
            'last_active' => $user->last_active,
            'mining_time' => $user->mining_time,
            'username' => $user->username,
            'username_count' => $user->username_count,
            'total_invite' => $user->total_invite,
            'invite_setup' => $user->invite_setup,
            'account_status' => $user->account_status,
            'ban_reason' => $user->ban_reason,
            'ban_date' => $user->ban_date,
            'join_date' => $user->join_date,
            'userGuide' => $userGuide,
            'auth_token' => $authToken
        ];

        return response()->json($response, 200);
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'country' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide all required fields.'
            ], 400);
        }

        // Check if user already exists
        $emailExists = User::where('email', $request->email)->exists();
        $phoneExists = User::where('phone', $request->phone)->exists();

        if ($emailExists || $phoneExists) {
            return response()->json([
                'success' => false,
                'is_email_found' => $emailExists,
                'is_phone_found' => $phoneExists
            ], 402);
        }

        // Create user
        $user = User::create([
            'name' => trim($request->name),
            'email' => trim($request->email),
            'phone' => trim($request->phone),
            'country' => trim($request->country),
            'password' => trim($request->password), // Note: Original uses plain password
            'token' => 0,
            'coin' => 0,
            'is_mining' => 0,
            'mining_end_time' => null,
            'last_active' => null,
            'mining_time' => 0,
            'username' => null,
            'username_count' => 0,
            'total_invite' => 0,
            'invite_setup' => 'not_setup',
            'account_status' => 'active',
            'ban_reason' => null,
            'ban_date' => null,
            'join_date' => now()->format('Y-m-d H:i:s'),
            'coin_end_time' => null,
            'total_coin_claim' => 0,
            'otp' => null,
        ]);

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User account created successfully.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create user account.'
        ], 401);
    }

    public function otpRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found or account is not active.'
            ], 404);
        }

        // Generate random 6-digit OTP
        $otp = mt_rand(100000, 999999);
        $user->update(['otp' => $otp]);

        // Send OTP via email (using external service)
        $postData = [
            'code' => $otp,
            'recipient' => $user->email
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://crutox.com/sendmail/forget_password.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to your email.'
        ]);
    }

    public function otpRequestNew(Request $request)
    {
        // Same as otpRequest (alias for compatibility)
        return $this->otpRequest($request);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and OTP code are required.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found or account not active.'
            ], 404);
        }

        if ($user->otp != $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.'
        ]);
    }

    public function verifyOtpAndSetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reason' => 'required|in:get,set',
            'code' => 'required_if:reason,set|numeric',
            'new_password' => 'required_if:reason,set|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found or account not active.'
            ], 404);
        }

        // If reason is 'get', verify OTP only
        if ($request->reason === 'get') {
            if ($user->otp != $request->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.'
            ]);
        }

        // If reason is 'set', verify OTP and set password
        if ($request->reason === 'set') {
            if ($user->otp != $request->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new OTP.'
                ], 400);
            }

            $user->update([
                'password' => $request->new_password,
                'otp' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid reason.'
        ], 400);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email, old password, and new password (min 8 characters) are required.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('password', $request->old_password)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or account not active.'
            ], 401);
        }

        $user->update(['password' => $request->new_password]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        // This is an alias that uses otpRequest + verifyOtpAndSetPassword flow
        // For convenience, we'll call otpRequest
        return $this->otpRequest($request);
    }

    public function getEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email or OTP not provided.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found or account is not verified.'
            ], 404);
        }

        // Update OTP in the database
        $user->update(['otp' => $request->otp]);

        return response()->json([
            'success' => true,
            'message' => 'OTP updated successfully.'
        ]);
    }
}
