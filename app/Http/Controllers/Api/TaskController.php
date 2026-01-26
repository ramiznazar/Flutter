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
        $task = SocialMediaSetting::where('ID', $request->task_id)->first();

        if (!$task) {
            // Get available task IDs for better error message
            $availableTasks = SocialMediaSetting::orderBy('ID', 'asc')
                ->limit(10)
                ->pluck('ID')
                ->toArray();
            
            return response()->json([
                'success' => false,
                'message' => 'Task not found. Available task IDs: ' . implode(', ', $availableTasks)
            ], 404);
        }

        // Check if task is active (if Status column exists)
        if (isset($task->Status) && $task->Status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Task is not active'
            ], 400);
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

        // For daily tasks, check if user already completed within last 24 hours
        if ($request->task_type === 'daily') {
            $now = Carbon::now();
            $twentyFourHoursAgo = $now->copy()->subHours(24);
            
            // Check if user claimed this task within last 24 hours
            $alreadyCompleted = TaskCompletion::where('user_id', $user->id)
                ->where('task_id', $request->task_id)
                ->where('task_type', 'daily')
                ->where('reward_claimed', 1)
                ->where('reward_claimed_at', '>=', $twentyFourHoursAgo)
                ->exists();

            if ($alreadyCompleted) {
                // Get the last claim time to show when it will be available again
                $lastClaim = TaskCompletion::where('user_id', $user->id)
                    ->where('task_id', $request->task_id)
                    ->where('task_type', 'daily')
                    ->where('reward_claimed', 1)
                    ->orderBy('reward_claimed_at', 'desc')
                    ->first();
                
                if ($lastClaim) {
                    $nextAvailableAt = Carbon::parse($lastClaim->reward_claimed_at)->addHours(24);
                    $secondsUntilAvailable = $now->diffInSeconds($nextAvailableAt);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Daily task already completed. Available again in 24 hours.',
                        'next_available_at' => $nextAvailableAt->format('Y-m-d H:i:s'),
                        'seconds_until_available' => $secondsUntilAvailable
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
        $task = SocialMediaSetting::where('ID', $request->task_id)->first();

        if (!$task) {
            // Get available task IDs for better error message
            $availableTasks = SocialMediaSetting::orderBy('ID', 'asc')
                ->limit(10)
                ->pluck('ID')
                ->toArray();
            
            return response()->json([
                'success' => false,
                'message' => 'Task not found. Available task IDs: ' . implode(', ', $availableTasks)
            ], 404);
        }

        // Check if task is active (if Status column exists)
        if (isset($task->Status) && $task->Status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Task is not active'
            ], 400);
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

            // Add coins to mining balance (token)
            $user->increment('token', $reward);
            
            // If mining is active, adjust mining_start_balance so balance calculation continues correctly
            if ($user->is_mining == 1 && $user->mining_start_balance !== null) {
                $user->increment('mining_start_balance', $reward);
            }
            
            $user->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reward claimed successfully',
                'reward' => $reward,
                'new_balance' => (float) $user->token,
                'is_mining_active' => $user->is_mining == 1
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

        $task = SocialMediaSetting::where('ID', $request->task_id)->first();

        if (!$task) {
            // Get available task IDs for better error message
            $availableTasks = SocialMediaSetting::orderBy('ID', 'asc')
                ->limit(10)
                ->pluck('ID')
                ->toArray();
            
            return response()->json([
                'success' => false,
                'message' => 'Task not found. Available task IDs: ' . implode(', ', $availableTasks)
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

    /**
     * Get daily tasks list with user's claim status
     */
    public function getDailyTasks(Request $request)
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

        // Get daily tasks (first 3 tasks)
        $tasks = SocialMediaSetting::orderBy('ID', 'asc')
            ->limit(3)
            ->get();

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

        // Get user's task completions
        $completions = TaskCompletion::where('user_id', $user->id)
            ->where('task_type', 'daily')
            ->whereIn('task_id', $tasks->pluck('ID'))
            ->where('reward_claimed', 1)
            ->where('reward_claimed_at', '>=', $twentyFourHoursAgo)
            ->get()
            ->keyBy('task_id');

        // Format tasks with status
        $tasksWithStatus = $tasks->map(function($task) use ($user, $completions, $now) {
            $taskId = $task->ID;
            $completion = $completions->get($taskId);
            
            $status = 'available'; // available, in_progress, claimable, claimed
            $rewardAvailable = false;
            $secondsRemaining = 0;
            $nextAvailableAt = null;
            $secondsUntilAvailable = 0;

            if ($completion) {
                // Task was claimed within 24 hours
                $claimedAt = Carbon::parse($completion->reward_claimed_at);
                $nextAvailable = $claimedAt->copy()->addHours(24);
                
                if ($now < $nextAvailable) {
                    $status = 'claimed';
                    $nextAvailableAt = $nextAvailable->format('Y-m-d H:i:s');
                    $secondsUntilAvailable = $now->diffInSeconds($nextAvailable);
                } else {
                    $status = 'available';
                }
            } else {
                // Check if there's an unclaimed task in progress
                $inProgress = TaskCompletion::where('user_id', $user->id)
                    ->where('task_id', $taskId)
                    ->where('task_type', 'daily')
                    ->where('reward_claimed', 0)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($inProgress) {
                    $availableAt = Carbon::parse($inProgress->reward_available_at);
                    
                    if ($now >= $availableAt) {
                        $status = 'claimable';
                        $rewardAvailable = true;
                    } else {
                        $status = 'in_progress';
                        $secondsRemaining = $now->diffInSeconds($availableAt);
                    }
                }
            }

            return [
                'id' => $task->ID,
                'name' => $task->Name,
                'reward' => (float) $task->Token,
                'redirect_link' => $task->Link,
                'icon' => $task->Icon,
                'status' => $status,
                'reward_available' => $rewardAvailable,
                'seconds_remaining' => $secondsRemaining,
                'next_available_at' => $nextAvailableAt,
                'seconds_until_available' => $secondsUntilAvailable
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tasksWithStatus
        ]);
    }
}
