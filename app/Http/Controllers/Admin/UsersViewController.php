<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBooster;
use App\Models\MysteryBoxClaim;
use App\Jobs\GiveCoinsToAllUsers;
use App\Jobs\GiveBoosterToAllUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;
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
                // Get active booster
                $user->active_booster = UserBooster::where('user_id', $user->id)
                    ->where('is_active', 1)
                    ->where('expires_at', '>', now())
                    ->orderBy('expires_at', 'desc')
                    ->first();

                // Get mystery box data
                $user->mystery_box_data = MysteryBoxClaim::where('user_id', $user->id)
                    ->orderBy('box_type')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($claim) {
                        return [
                            'box_type' => $claim->box_type,
                            'clicks' => $claim->clicks ?? 0,
                            'ads_watched' => $claim->ads_watched ?? 0,
                            'ads_required' => $claim->ads_required ?? 0,
                            'box_opened' => (bool)($claim->box_opened ?? 0),
                            'reward_coins' => $claim->reward_coins,
                            'last_clicked_at' => $claim->last_clicked_at,
                            'last_ad_watched_at' => $claim->last_ad_watched_at,
                            'cooldown_until' => $claim->cooldown_until,
                            'opened_at' => $claim->opened_at,
                            'created_at' => $claim->created_at,
                        ];
                    });

                return $user;
            });

        return view('admin.users.index', compact('users', 'search', 'page', 'perPage', 'total'));
    }

    public function giveCoins(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:specific,all',
            'user_identifier' => 'required_if:target_type,specific|string|nullable',
            'coin_amount' => 'required|numeric',
        ]);

        $targetType = $request->target_type;
        $coinAmount = (float) $request->coin_amount;

        if ($targetType === 'all') {
            // Dispatch job after response is sent to ensure immediate return
            GiveCoinsToAllUsers::dispatchAfterResponse($coinAmount);

            $message = "Coins distribution job has been queued. Amount: $coinAmount coins per user. The process will run in the background and may take a few minutes depending on the number of users.";
            
            // Return JSON for AJAX requests, otherwise redirect
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.users.index')
                ]);
            }
            
            return back()->with('message', $message)
                ->with('messageType', 'info');
        } else {
            // Give coins to specific user
            $identifier = $request->user_identifier;

            $user = User::where(function($q) use ($identifier) {
                    $q->where('email', $identifier)
                      ->orWhere('username', $identifier)
                      ->orWhere('id', $identifier);
                })
                ->where('account_status', 'active')
                ->first();

            if (!$user) {
                $message = 'User not found or account is not active.';
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 404);
                }
                return back()->with('message', $message)->with('messageType', 'danger');
            }

            $currentCoins = (float) $user->coin;
            $newCoins = $currentCoins + $coinAmount;

            if ($newCoins < 0) {
                $message = 'Insufficient coins. User has ' . $currentCoins . ' coins.';
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('message', $message)->with('messageType', 'danger');
            }

            // Update both coin (spending currency) and token (mining balance)
            $currentToken = (float) $user->token;
            $newToken = $currentToken + $coinAmount;
            
            // If user is mining, also adjust mining_start_balance to maintain accuracy
            $updateData = [
                'coin' => $newCoins,
                'token' => $newToken
            ];
            
            if ($user->is_mining == 1 && $user->mining_start_balance !== null) {
                $updateData['mining_start_balance'] = (float) $user->mining_start_balance + $coinAmount;
            }
            
            $user->update($updateData);

            $message = 'Coins updated successfully. Previous balance: ' . $currentCoins . ', New balance: ' . $newCoins . '.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.users.index')
                ]);
            }
            return back()->with('message', $message)->with('messageType', 'success');
        }
    }

    public function giveBooster(Request $request)
    {
        $request->validate([
            'booster_target_type' => 'required|in:specific,all',
            'booster_user_identifier' => 'required_if:booster_target_type,specific|string|nullable',
            'booster_type' => 'required|string',
            'booster_duration' => 'required|numeric|min:0.1|max:24',
        ]);

        $targetType = $request->booster_target_type;
        $boosterType = $request->booster_type;
        $durationHours = (float) $request->booster_duration;

        // Calculate expiry time
        $durationSeconds = (int) ($durationHours * 3600);
        $now = Carbon::now();
        $expiresAt = $now->copy()->addSeconds($durationSeconds);

        if ($targetType === 'all') {
            // Dispatch job after response is sent to ensure immediate return
            GiveBoosterToAllUsers::dispatchAfterResponse($boosterType, $expiresAt);

            $message = "Booster ($boosterType) distribution job has been queued. Duration: " . $request->booster_duration . " hours. Expires at: " . $expiresAt->format('Y-m-d H:i:s') . ". The process will run in the background and may take a few minutes depending on the number of users.";
            
            // Return JSON for AJAX requests, otherwise redirect
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.users.index')
                ]);
            }
            
            return back()->with('message', $message)
                ->with('messageType', 'info');
        } else {
            // Give booster to specific user
            $identifier = $request->booster_user_identifier;

            $user = User::where(function($q) use ($identifier) {
                    $q->where('email', $identifier)
                      ->orWhere('username', $identifier)
                      ->orWhere('id', $identifier);
                })
                ->where('account_status', 'active')
                ->first();

            if (!$user) {
                $message = 'User not found or account is not active.';
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 404);
                }
                return back()->with('message', $message)->with('messageType', 'danger');
            }

            // Deactivate any expired boosters
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

            $message = "Booster ($boosterType) assigned successfully. Expires at: " . $expiresAt->format('Y-m-d H:i:s');
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.users.index')
                ]);
            }
            return back()->with('message', $message)->with('messageType', 'success');
        }
    }

    public function resetMysteryBox(Request $request)
    {
        $request->validate([
            'mystery_box_user_identifier' => 'required|string',
            'mystery_box_type' => 'required|in:all,common,rare,epic,legendary',
        ]);

        $identifier = $request->mystery_box_user_identifier;
        $boxType = $request->mystery_box_type;

        $user = User::where(function($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
            })
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return back()->with('message', 'User not found or account is not active.')
                ->with('messageType', 'danger');
        }

        if ($boxType === 'all') {
            $affectedRows = MysteryBoxClaim::where('user_id', $user->id)->delete();
            $message = "All mystery box data reset successfully for user {$user->email}. Affected records: $affectedRows";
        } else {
            $affectedRows = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->delete();
            $message = "Mystery box data for '$boxType' reset successfully for user {$user->email}. Affected records: $affectedRows";
        }

        return back()->with('message', $message)
            ->with('messageType', 'success');
    }
}















