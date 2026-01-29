<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaSetting;
use App\Models\Setting;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TasksViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $editId = $request->get('edit_id');
        $editTask = null;
        
        if ($editId) {
            $editTask = SocialMediaSetting::find($editId);
        }

        // Get daily tasks (first 3)
        $dailyTasks = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->get();
        
        // Get one-time tasks (all after first 3)
        // Get IDs of first 3 tasks to exclude them
        $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
        $onetimeTasks = SocialMediaSetting::orderBy('id', 'desc')
            ->whereNotIn('id', $dailyTaskIds)
            ->get();
        
        // Ensure daily_tasks_reset_time column exists
        $this->ensureSettingsColumnExists('daily_tasks_reset_time');

        // Get reset time
        $settings = Setting::first();
        $resetTime = $settings ? $settings->daily_tasks_reset_time : null;

        // Get all task completions with user and task info
        $taskCompletions = TaskCompletion::with(['user', 'task'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(500) // Limit to last 500 records for performance
            ->get();

        return view('admin.tasks.index', compact('dailyTasks', 'onetimeTasks', 'editTask', 'resetTime', 'taskCompletions'));
    }

    public function storeDaily(Request $request)
    {
        $request->validate([
            'task1_name' => 'required|string',
            'task1_reward' => 'required|integer|min:0',
            'task1_link' => 'required|url',
            'task2_name' => 'required|string',
            'task2_reward' => 'required|integer|min:0',
            'task2_link' => 'required|url',
            'task3_name' => 'required|string',
            'task3_reward' => 'required|integer|min:0',
            'task3_link' => 'required|url',
            'reset_time' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Get IDs of first 3 tasks (matching PHP behavior)
            $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
            
            // Delete existing first 3 tasks by IDs
            if (!empty($dailyTaskIds)) {
                SocialMediaSetting::whereIn('id', $dailyTaskIds)->delete();
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

            foreach ($tasks as $task) {
                $taskData = [
                    'Name' => $task['name'],
                    'Link' => $task['link'],
                    'Token' => $task['reward'],
                    'Icon' => $task['icon'],
                ];
                
                // Only add task_type and Status if columns exist
                if (Schema::hasColumn('social_media_setting', 'task_type')) {
                    $taskData['task_type'] = 'daily';
                }
                if (Schema::hasColumn('social_media_setting', 'Status')) {
                    $taskData['Status'] = 1;
                }
                
                SocialMediaSetting::create($taskData);
            }

            // Ensure daily_tasks_reset_time column exists before updating (matching PHP behavior)
            $this->ensureSettingsColumnExists('daily_tasks_reset_time');

            // Update reset time
            Setting::updateOrCreateSettings(['daily_tasks_reset_time' => $request->reset_time]);

            DB::commit();

            return redirect()->route('admin.tasks.index')
                ->with('message', 'Daily tasks updated successfully.')
                ->with('messageType', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('message', 'Error updating daily tasks: ' . $e->getMessage())
                ->with('messageType', 'danger');
        }
    }

    public function storeOnetime(Request $request)
    {
        $request->validate([
            'task_name' => 'required|string',
            'reward' => 'required|integer|min:0',
            'redirect_link' => 'required|url',
        ]);

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

        SocialMediaSetting::create([
            'Name' => $request->task_name,
            'Link' => $request->redirect_link,
            'Token' => $request->reward,
            'Icon' => $request->icon ?? 'https://img.icons8.com/color/48/000000/task.png',
            'task_type' => 'onetime',
            'Status' => ($request->status ?? 'active') === 'active' ? 1 : 0
        ]);

        return redirect()->route('admin.tasks.index')
            ->with('message', 'One-time task created successfully.')
            ->with('messageType', 'success');
    }

    public function updateOnetime(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:social_media_setting,id',
            'task_name' => 'required|string',
            'reward' => 'required|integer|min:0',
            'redirect_link' => 'required|url',
        ]);

        $task = SocialMediaSetting::findOrFail($request->task_id);

        // Check if it's a daily task (first 3)
        $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
        
        if (in_array($request->task_id, $dailyTaskIds)) {
            return back()->with('message', 'Cannot update daily tasks from here. Use the daily tasks form.')
                ->with('messageType', 'danger');
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

        return redirect()->route('admin.tasks.index')
            ->with('message', 'Task updated successfully.')
            ->with('messageType', 'success');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:social_media_setting,ID',
        ]);

        $task = SocialMediaSetting::findOrFail($request->task_id);

        // Check if it's a daily task (first 3)
        $dailyTaskIds = SocialMediaSetting::orderBy('id', 'asc')->limit(3)->pluck('id')->toArray();
        
        if (in_array($request->task_id, $dailyTaskIds)) {
            return back()->with('message', 'Cannot delete daily tasks. Update them instead.')
                ->with('messageType', 'danger');
        }

        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('message', 'Task deleted successfully.')
            ->with('messageType', 'success');
    }

    /**
     * Ensure that a specific column exists in the settings table
     * Dynamically adds the column if it doesn't exist (matching PHP behavior)
     */
    private function ensureSettingsColumnExists($column)
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $columnDefinitions = [
            'daily_tasks_reset_time' => ['type' => 'dateTime', 'params' => [], 'default' => null, 'after' => 'goal_users'],
        ];

        if (!Schema::hasColumn('settings', $column)) {
            $def = $columnDefinitions[$column] ?? null;
            
            if ($def) {
                $after = $def['after'] ?? null;
                $afterColumnExists = $after ? Schema::hasColumn('settings', $after) : false;
                
                Schema::table('settings', function (Blueprint $table) use ($column, $def, $after, $afterColumnExists) {
                    $method = $def['type'];
                    $params = $def['params'] ?? [];

                    if ($method === 'dateTime') {
                        $columnObj = $table->dateTime($column)->nullable();
                    } elseif ($method === 'integer') {
                        $columnObj = $table->integer($column)->default($def['default'] ?? null)->nullable();
                    } elseif ($method === 'decimal') {
                        $precision = $params[0] ?? 8;
                        $scale = $params[1] ?? 2;
                        $columnObj = $table->decimal($column, $precision, $scale)->default($def['default'] ?? null)->nullable();
                    } else {
                        $columnObj = $table->$method($column, ...$params);
                        if (isset($def['default'])) {
                            $columnObj = $columnObj->default($def['default']);
                        }
                        $columnObj = $columnObj->nullable();
                    }

                    // Only use 'after' if the referenced column exists
                    if ($after && $afterColumnExists) {
                        $columnObj->after($after);
                    }
                });
            }
        }
    }
}





