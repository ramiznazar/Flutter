<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBooster;
use App\Models\MysteryBoxClaim;
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
            'user_identifier' => 'required|string',
            'coin_amount' => 'required|numeric',
        ]);

        $identifier = $request->user_identifier;
        $coinAmount = (float) $request->coin_amount;

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

        $currentCoins = (float) $user->coin;
        $newCoins = $currentCoins + $coinAmount;

        if ($newCoins < 0) {
            return back()->with('message', 'Insufficient coins. User has ' . $currentCoins . ' coins.')
                ->with('messageType', 'danger');
        }

        $user->update(['coin' => $newCoins]);

        return back()->with('message', 'Coins updated successfully. Previous balance: ' . $currentCoins . ', New balance: ' . $newCoins . '.')
            ->with('messageType', 'success');
    }

    public function giveBooster(Request $request)
    {
        $request->validate([
            'booster_user_identifier' => 'required|string',
            'booster_type' => 'required|string',
            'booster_duration' => 'required|numeric|min:0.1|max:24',
        ]);

        $identifier = $request->booster_user_identifier;
        $boosterType = $request->booster_type;
        $durationHours = (float) $request->booster_duration;

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

        // Deactivate any expired boosters
        UserBooster::where('user_id', $user->id)
            ->where('expires_at', '<=', Carbon::now())
            ->update(['is_active' => 0]);

        // Calculate expiry time
        $durationSeconds = (int) ($durationHours * 3600);
        $expiresAt = Carbon::now()->addSeconds($durationSeconds);

        // Create new booster
        $now = Carbon::now();
        UserBooster::create([
            'user_id' => $user->id,
            'booster_type' => $boosterType,
            'started_at' => $now,
            'expires_at' => $expiresAt,
            'is_active' => 1,
            'created_at' => $now
        ]);

        return back()->with('message', "Booster ($boosterType) assigned successfully. Expires at: " . $expiresAt->format('Y-m-d H:i:s'))
            ->with('messageType', 'success');
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















