<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\MysteryBoxClaim;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email and password are required.'
            ], 400);
        }

        // Check hardcoded credentials first (for backward compatibility)
        $hardcodedEmail = "admin@crutox.com";
        $hardcodedPassword = "admin$$$@@@";

        if ($request->email === $hardcodedEmail && $request->password === $hardcodedPassword) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Login successful'
            ]);
        }

        // Check database admin table
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password. Please try again.'
            ], 401);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password. Please try again.'
            ], 401);
        }

        // Update last login
        $admin->update(['last_login' => now()]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Login successful',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'username' => $admin->username
            ]
        ]);
    }

    public function mysteryBoxReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
            'box_type' => 'nullable|string|in:common,rare,epic,legendary,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'User identifier is required.'
            ], 400);
        }

        $identifier = trim($request->user_identifier);
        $boxType = $request->box_type ?? 'all';

        // Validate box type if provided
        if ($boxType && !in_array($boxType, ['common', 'rare', 'epic', 'legendary', 'all'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid box type. Use: common, rare, epic, legendary, or all'
            ], 400);
        }

        // Find user by email, username, or ID
        $user = User::where(function($query) use ($identifier) {
            $query->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
        })->where('account_status', 'active')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account is not active.'
            ], 404);
        }

        // Reset mystery box data
        if ($boxType === 'all' || !$boxType) {
            $affectedRows = MysteryBoxClaim::where('user_id', $user->id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All mystery box data reset successfully for user.',
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_username' => $user->username,
                'affected_records' => $affectedRows,
                'reset_type' => 'all'
            ]);
        } else {
            $affectedRows = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Mystery box data for '$boxType' reset successfully for user.",
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_username' => $user->username,
                'box_type' => $boxType,
                'affected_records' => $affectedRows,
                'reset_type' => 'specific'
            ]);
        }
    }

    public function userStatsManage(Request $request)
    {
        $method = $request->method();
        
        // GET - Get user stats
        if ($method === 'GET') {
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

            $userLevel = UserLevel::where('user_id', $user->id)->first();
            $miningSessions = $userLevel ? (int)$userLevel->mining_session : 0;
            $referrals = (int)$user->total_invite;

            return response()->json([
                'success' => true,
                'data' => [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'mining_sessions' => $miningSessions,
                    'referrals' => $referrals
                ]
            ]);
        }

        // POST - Update user stats
        if ($method === 'POST') {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'mining_sessions' => 'nullable|integer|min:0',
                'referrals' => 'nullable|integer|min:0',
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

            $updates = [];

            // Update mining sessions
            if ($request->has('mining_sessions') && $request->mining_sessions !== null) {
                $miningSessions = (int)$request->mining_sessions;
                
                $userLevel = UserLevel::where('user_id', $user->id)->first();
                
                if ($userLevel) {
                    $userLevel->update(['mining_session' => $miningSessions]);
                } else {
                    UserLevel::create([
                        'user_id' => $user->id,
                        'mining_session' => $miningSessions,
                        'spin_wheel' => 0,
                        'current_level' => 1,
                        'achieved_at' => now()
                    ]);
                }
                $updates[] = 'mining_sessions';
            }

            // Update referrals
            if ($request->has('referrals') && $request->referrals !== null) {
                $referrals = (int)$request->referrals;
                $user->update(['total_invite' => $referrals]);
                $updates[] = 'referrals';
            }

            if (empty($updates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid updates provided'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'User stats updated successfully',
                'updated_fields' => $updates
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
}
