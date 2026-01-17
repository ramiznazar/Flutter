<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UsersManageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 20);
        $offset = ($page - 1) * $perPage;

        $query = User::where('account_status', 'active');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $total = $query->count();
        $users = $query->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'user_id' => 'USR' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                    'username' => $user->username ?: 'N/A',
                    'email' => $user->email,
                    'name' => $user->name,
                    'coins_balance' => (float) $user->coin,
                    'type' => 'user',
                    'join_date' => $user->join_date
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    public function giveCoins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_type' => 'sometimes|in:specific,all',
            'user_identifier' => 'required_if:target_type,specific|string|nullable',
            'coin_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $targetType = $request->target_type ?? 'specific'; // Default to specific for backward compatibility
        $coinAmount = (float) $request->coin_amount;

        if ($targetType === 'all') {
            // Give coins to all active users
            $users = User::where('account_status', 'active')->get();
            $successCount = 0;
            $failedCount = 0;

            foreach ($users as $user) {
                $currentCoins = (float) $user->coin;
                $newCoins = $currentCoins + $coinAmount;

                // Only update if new coins won't be negative
                if ($newCoins >= 0) {
                    $user->update(['coin' => $newCoins]);
                    $successCount++;
                } else {
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Coins updated for $successCount users. Amount: $coinAmount coins per user." . ($failedCount > 0 ? " $failedCount users were skipped due to insufficient balance." : ''),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'amount' => $coinAmount
            ]);
        } else {
            // Give coins to specific user
            $identifier = $request->user_identifier;

            if (empty($identifier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User identifier is required for specific user target.'
                ], 400);
            }

            $user = User::where(function($q) use ($identifier) {
                    $q->where('email', $identifier)
                      ->orWhere('username', $identifier)
                      ->orWhere('id', $identifier);
                })
                ->where('account_status', 'active')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or account is not active.'
                ], 404);
            }

            $currentCoins = (float) $user->coin;
            $newCoins = $currentCoins + $coinAmount;

            if ($newCoins < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient coins. User has ' . $currentCoins . ' coins.'
                ], 400);
            }

            $user->update(['coin' => $newCoins]);

            return response()->json([
                'success' => true,
                'message' => 'Coins updated successfully.',
                'previous_balance' => $currentCoins,
                'new_balance' => $newCoins,
                'amount' => $coinAmount
            ]);
        }
    }

    public function giveBooster(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_type' => 'sometimes|in:specific,all',
            'user_identifier' => 'required_if:target_type,specific|string|nullable',
            'booster_type' => 'required|string',
            'booster_duration' => 'required|numeric|min:0.1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $targetType = $request->target_type ?? 'specific'; // Default to specific for backward compatibility
        $boosterType = $request->booster_type;
        $durationHours = (float) $request->booster_duration;

        // Calculate expiry time
        $durationSeconds = (int) ($durationHours * 3600);
        $now = Carbon::now();
        $expiresAt = $now->copy()->addSeconds($durationSeconds);

        if ($targetType === 'all') {
            // Give booster to all active users
            $users = User::where('account_status', 'active')->get();
            $successCount = 0;

            foreach ($users as $user) {
                // Deactivate any expired boosters for this user
                UserBooster::where('user_id', $user->id)
                    ->where('expires_at', '<=', Carbon::now())
                    ->update(['is_active' => 0]);

                // Create new booster
                UserBooster::create([
                    'user_id' => $user->id,
                    'booster_type' => $boosterType,
                    'started_at' => $now,
                    'expires_at' => $expiresAt,
                    'is_active' => 1,
                    'created_at' => $now
                ]);

                $successCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Booster assigned successfully to $successCount users.",
                'booster_type' => $boosterType,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'duration_hours' => $durationHours,
                'users_affected' => $successCount
            ]);
        } else {
            // Give booster to specific user
            $identifier = $request->user_identifier;

            if (empty($identifier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User identifier is required for specific user target.'
                ], 400);
            }

            $user = User::where(function($q) use ($identifier) {
                    $q->where('email', $identifier)
                      ->orWhere('username', $identifier)
                      ->orWhere('id', $identifier);
                })
                ->where('account_status', 'active')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or account is not active.'
                ], 404);
            }

            // Deactivate any expired boosters
            UserBooster::where('user_id', $user->id)
                ->where('expires_at', '<=', Carbon::now())
                ->update(['is_active' => 0]);

            // Create new booster
            $booster = UserBooster::create([
                'user_id' => $user->id,
                'booster_type' => $boosterType,
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'is_active' => 1,
                'created_at' => $now
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booster assigned successfully.',
                'booster_type' => $boosterType,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'duration_hours' => $durationHours
            ]);
        }
    }

    public function getUserStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'User identifier is required.'
            ], 400);
        }

        $identifier = $request->user_identifier;

        $user = User::where(function($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Get mining sessions from user_levels
        $userLevel = \App\Models\UserLevel::where('user_id', $user->id)->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;
        $referrals = (int) $user->total_invite;

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'mining_sessions' => $miningSessions,
                'referrals' => $referrals
            ]
        ]);
    }

    public function updateUserStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'User identifier is required.'
            ], 400);
        }

        $identifier = $request->user_identifier;

        $user = User::where(function($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $updates = [];

        // Update mining sessions
        if ($request->has('mining_sessions') && $request->mining_sessions !== null) {
            $miningSessions = (int) $request->mining_sessions;
            if ($miningSessions >= 0) {
                $userLevel = \App\Models\UserLevel::where('user_id', $user->id)->first();
                if ($userLevel) {
                    $userLevel->update(['mining_session' => $miningSessions]);
                } else {
                    \App\Models\UserLevel::create([
                        'user_id' => $user->id,
                        'mining_session' => $miningSessions,
                        'spin_wheel' => 0,
                        'current_level' => 1,
                        'achieved_at' => now()
                    ]);
                }
                $updates[] = 'mining_sessions';
            }
        }

        // Update referrals
        if ($request->has('referrals') && $request->referrals !== null) {
            $referrals = (int) $request->referrals;
            if ($referrals >= 0) {
                $user->update(['total_invite' => $referrals]);
                $updates[] = 'referrals';
            }
        }

        if (empty($updates)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid updates provided.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'User stats updated successfully.',
            'updated_fields' => $updates
        ]);
    }

    public function getUserCoinSpeed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'User identifier is required.'
            ], 400);
        }

        $identifier = $request->user_identifier;

        $user = User::where(function($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Get overall coin speed settings
        $settings = \App\Models\Setting::first();
        $overallSpeed = $settings ? (float) $settings->mining_speed : 10.00;
        $baseRate = $settings ? (float) $settings->base_mining_rate : 5.00;
        $maxSpeed = $settings ? (float) $settings->max_mining_speed : 50.00;

        // Get user-specific coin speed if exists (will be null if not set)
        $userCoinSpeed = $user->custom_coin_speed ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'overall_mining_speed' => $overallSpeed,
                'overall_base_rate' => $baseRate,
                'overall_max_speed' => $maxSpeed,
                'user_custom_coin_speed' => $userCoinSpeed,
                'effective_speed' => $userCoinSpeed ?? $overallSpeed
            ]
        ]);
    }

    public function updateUserCoinSpeed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
            'coin_speed' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'User identifier is required and coin speed must be a positive number.'
            ], 400);
        }

        $identifier = $request->user_identifier;

        $user = User::where(function($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $updateData = [];
        if ($request->has('coin_speed')) {
            // If coin_speed is null or empty, remove custom speed (use overall)
            if ($request->coin_speed === null || $request->coin_speed === '') {
                $updateData['custom_coin_speed'] = null;
            } else {
                $updateData['custom_coin_speed'] = (float) $request->coin_speed;
            }
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User coin speed updated successfully.',
            'user_custom_coin_speed' => $user->custom_coin_speed
        ]);
    }
}
