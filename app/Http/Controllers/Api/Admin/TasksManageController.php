<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TasksManageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        if ($type === 'daily') {
            // Get first 3 tasks as daily tasks
            $tasks = SocialMediaSetting::orderBy('id', 'asc')
                ->limit(3)
                ->get()
                ->map(function($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->Name,
                        'reward' => $task->Token,
                        'redirect_link' => $task->Link,
                        'icon' => $task->Icon
                    ];
                });

            $settings = Setting::first();
            $resetTime = $settings ? $settings->daily_tasks_reset_time : null;

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'reset_time' => $resetTime
            ]);
        } elseif ($type === 'onetime') {
            // Get tasks after first 3 as one-time tasks
            // Get IDs of first 3 tasks to exclude them
            $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
            $tasks = SocialMediaSetting::orderBy('id', 'desc')
                ->whereNotIn('id', $dailyTaskIds)
                ->get()
                ->map(function($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->Name,
                        'reward' => $task->Token,
                        'redirect_link' => $task->Link,
                        'icon' => $task->Icon,
                        'status' => 'active'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tasks
            ]);
        } else {
            // Get all tasks
            $allTasks = SocialMediaSetting::orderBy('id', 'asc')->get();
            
            $dailyTasks = $allTasks->take(3)->values();
            $onetimeTasks = $allTasks->skip(3)->values();

            return response()->json([
                'success' => true,
                'daily_tasks' => $dailyTasks,
                'onetime_tasks' => $onetimeTasks
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_type' => 'required|in:daily,onetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Task type is required.'
            ], 400);
        }

        if ($request->task_type === 'daily') {
            // Update/create 3 daily tasks
            $validator = Validator::make($request->all(), [
                'task1_name' => 'required|string',
                'task1_reward' => 'required|integer',
                'task1_link' => 'required|string',
                'task2_name' => 'required|string',
                'task2_reward' => 'required|integer',
                'task2_link' => 'required|string',
                'task3_name' => 'required|string',
                'task3_reward' => 'required|integer',
                'task3_link' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'All three daily tasks are required.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Delete existing first 3 tasks
                SocialMediaSetting::orderBy('id', 'asc')->limit(3)->delete();

                // Create new daily tasks
                $tasks = [
                    [
                        'name' => $request->task1_name,
                        'reward' => $request->task1_reward,
                        'link' => $request->task1_link,
                        'icon' => $request->task1_icon ?? 'https://img.icons8.com/color/48/000000/task.png'
                    ],
                    [
                        'name' => $request->task2_name,
                        'reward' => $request->task2_reward,
                        'link' => $request->task2_link,
                        'icon' => $request->task2_icon ?? 'https://img.icons8.com/color/48/000000/task.png'
                    ],
                    [
                        'name' => $request->task3_name,
                        'reward' => $request->task3_reward,
                        'link' => $request->task3_link,
                        'icon' => $request->task3_icon ?? 'https://img.icons8.com/color/48/000000/task.png'
                    ]
                ];

                // Check if task_type and Status columns exist, if not add them (matching PHP behavior)
                if (!Schema::hasColumn('social_media_setting', 'task_type')) {
                    Schema::table('social_media_setting', function (Blueprint $table) {
                        $table->string('task_type', 50)->default('onetime')->nullable()->after('Token');
                    });
                }
                if (!Schema::hasColumn('social_media_setting', 'Status')) {
                    Schema::table('social_media_setting', function (Blueprint $table) {
                        $table->boolean('Status')->default(1)->nullable()->after('task_type');
                    });
                }

                foreach ($tasks as $task) {
                    SocialMediaSetting::create([
                        'Name' => $task['name'],
                        'Link' => $task['link'],
                        'Token' => $task['reward'],
                        'Icon' => $task['icon'],
                        'task_type' => 'daily',
                        'Status' => 1
                    ]);
                }

                // Update reset time if provided
                if ($request->has('reset_time')) {
                    $settings = Setting::first();
                    if ($settings) {
                        $settings->update(['daily_tasks_reset_time' => $request->reset_time]);
                    } else {
                        Setting::create(['daily_tasks_reset_time' => $request->reset_time]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Daily tasks updated successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update daily tasks: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Create one-time task
            $validator = Validator::make($request->all(), [
                'task_name' => 'required|string',
                'reward' => 'required|integer',
                'redirect_link' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task name, reward, and redirect link are required.'
                ], 400);
            }

            // Check if task_type and Status columns exist, if not add them (matching PHP behavior)
            if (!Schema::hasColumn('social_media_setting', 'task_type')) {
                Schema::table('social_media_setting', function (Blueprint $table) {
                    $table->string('task_type', 50)->default('onetime')->nullable()->after('Token');
                });
            }
            if (!Schema::hasColumn('social_media_setting', 'Status')) {
                Schema::table('social_media_setting', function (Blueprint $table) {
                    $table->boolean('Status')->default(1)->nullable()->after('task_type');
                });
            }

            $task = SocialMediaSetting::create([
                'Name' => $request->task_name,
                'Link' => $request->redirect_link,
                'Token' => $request->reward,
                'Icon' => $request->icon ?? '',
                'task_type' => 'onetime',
                'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'One-time task created successfully.'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'task_name' => 'required|string',
            'reward' => 'required|integer',
            'redirect_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID, task name, reward, and redirect link are required.'
            ], 400);
        }

        $task = SocialMediaSetting::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Check if it's a daily task (first 3)
        $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
        
        if (in_array($id, $dailyTaskIds)) {
            // For daily tasks, we might want to prevent certain updates
            // But allow updates for now
        }

        // Check if Status column exists, if not add it (matching PHP behavior)
        if (!Schema::hasColumn('social_media_setting', 'Status')) {
            Schema::table('social_media_setting', function (Blueprint $table) {
                $table->boolean('Status')->default(1)->nullable()->after('task_type');
            });
        }

        $updateData = [
            'Name' => $request->task_name,
            'Link' => $request->redirect_link,
            'Token' => $request->reward,
            'Icon' => $request->icon ?? $task->Icon,
        ];

        // Only update Status if column exists
        if (Schema::hasColumn('social_media_setting', 'Status')) {
            $updateData['Status'] = ($request->status ?? 'active') === 'active' ? 1 : 0;
        }

        $task->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $task = SocialMediaSetting::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Check if it's a daily task (first 3) - cannot delete
        $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
        
        if (in_array($id, $dailyTaskIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete daily tasks. Update them instead.'
            ], 400);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.'
        ]);
    }
}
