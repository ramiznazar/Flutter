<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TaskCompletion;
use App\Models\SocialMediaSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function taskStart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'task_id' => 'required|integer',
            'task_type' => 'required|in:daily,onetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        // Validate user
        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        // Check if task exists
        $task = SocialMediaSetting::find($request->task_id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Check if user already has an active completion for this task
        $existing = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $request->task_id)
            ->where('task_type', $request->task_type)
            ->where('reward_claimed', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existing) {
            $now = Carbon::now();
            $availableAt = Carbon::parse($existing->reward_available_at);

            if ($now >= $availableAt) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task already started. Reward is available.',
                    'reward_available' => true,
                    'reward_available_at' => $existing->reward_available_at
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Task already in progress.',
                    'reward_available' => false,
                    'reward_available_at' => $existing->reward_available_at,
                    'seconds_remaining' => $now->diffInSeconds($availableAt)
                ]);
            }
        }

        // For daily tasks, check if user already completed today
        if ($request->task_type === 'daily') {
            $settings = Setting::first();
            $resetTime = $settings ? $settings->daily_tasks_reset_time : null;

            if ($resetTime) {
                $alreadyCompleted = TaskCompletion::where('user_id', $user->id)
                    ->where('task_id', $request->task_id)
                    ->where('task_type', 'daily')
                    ->where('started_at', '>=', $resetTime)
                    ->where('reward_claimed', 1)
                    ->exists();

                if ($alreadyCompleted) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Daily task already completed today'
                    ], 400);
                }
            }
        }

        // Calculate reward available time
        $now = Carbon::now();
        $rewardAvailableAt = $request->task_type === 'daily' 
            ? $now->copy()->addMinutes(5) 
            : $now->copy()->addHour();

        // Create task completion
        $completion = TaskCompletion::create([
            'user_id' => $user->id,
            'task_id' => $request->task_id,
            'task_type' => $request->task_type,
            'started_at' => $now,
            'reward_available_at' => $rewardAvailableAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task started successfully',
            'reward_available_at' => $rewardAvailableAt->format('Y-m-d H:i:s'),
            'seconds_remaining' => $now->diffInSeconds($rewardAvailableAt),
            'task_type' => $request->task_type,
            'reward' => (float) $task->Token
        ]);
    }

    public function taskClaimReward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'task_id' => 'required|integer',
            'task_type' => 'required|in:daily,onetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        // Validate user
        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        // Get task reward
        $task = SocialMediaSetting::find($request->task_id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $reward = (float) $task->Token;

        // Find unclaimed completion
        $completion = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $request->task_id)
            ->where('task_type', $request->task_type)
            ->where('reward_claimed', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$completion) {
            return response()->json([
                'success' => false,
                'message' => 'No active task found. Please start the task first.'
            ], 404);
        }

        $now = Carbon::now();
        $availableAt = Carbon::parse($completion->reward_available_at);

        // Backend enforces timer
        if ($now < $availableAt) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not yet available. Timer still running.',
                'seconds_remaining' => $now->diffInSeconds($availableAt),
                'reward_available_at' => $completion->reward_available_at
            ], 400);
        }

        // Mark reward as claimed and give coins to user
        DB::beginTransaction();

        try {
            $completion->update([
                'reward_claimed' => 1,
                'reward_claimed_at' => $now
            ]);

            $user->increment('token', $reward);
            $user->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reward claimed successfully',
                'reward' => $reward,
                'new_balance' => (float) $user->token
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error claiming reward: ' . $e->getMessage()
            ], 500);
        }
    }

    public function trackTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'task_id' => 'required|integer',
            'task_type' => 'required|in:daily,onetime',
            'action' => 'sometimes|string', // e.g., 'started', 'completed', 'claimed'
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

        $task = SocialMediaSetting::find($request->task_id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Track task interaction - this creates a record of the user's activity
        // The existing task_start already tracks, but this endpoint is specifically for tracking
        $action = $request->input('action', 'viewed');

        // Create or update task completion tracking
        $completion = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $request->task_id)
            ->where('task_type', $request->task_type)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$completion) {
            // Create a tracking record (but don't mark as started unless action is 'started')
            $completion = TaskCompletion::create([
                'user_id' => $user->id,
                'task_id' => $request->task_id,
                'task_type' => $request->task_type,
                'started_at' => $action === 'started' ? Carbon::now() : null,
                'reward_available_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task tracked successfully',
            'action' => $action,
            'task_id' => $request->task_id,
            'task_type' => $request->task_type
        ]);
    }
}
