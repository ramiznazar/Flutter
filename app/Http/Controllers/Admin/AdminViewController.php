<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AdminViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard()
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist(['current_users', 'goal_users']);

        // Get actual user count from users table (real users)
        $actualUserCount = User::count();

        // Get current settings (manual/fake values from settings table)
        $settings = Setting::first();
        $currentSettings = [
            'current_users' => $settings->current_users ?? 99000,
            'goal_users' => $settings->goal_users ?? 1000000,
            'real_users' => $actualUserCount
        ];

        $progressPercent = $currentSettings['goal_users'] > 0 
            ? ($currentSettings['current_users'] / $currentSettings['goal_users'] * 100) 
            : 0;
        if ($progressPercent > 100) $progressPercent = 100;

        return view('admin.dashboard', compact('currentSettings', 'progressPercent'));
    }

    public function updateUserCount(Request $request)
    {
        $request->validate([
            'current_users' => 'required|integer|min:0',
            'goal_users' => 'required|integer|min:1',
        ]);

        // Ensure columns exist before updating (matching PHP behavior)
        $this->ensureSettingsColumnsExist(['current_users', 'goal_users']);

        $settings = Setting::first();
        
        Setting::updateOrCreateSettings([
            'current_users' => $request->current_users,
            'goal_users' => $request->goal_users
        ]);

        return redirect()->route('admin.dashboard')
            ->with('message', 'User count updated successfully.')
            ->with('messageType', 'success');
    }

    /**
     * Ensure that specified columns exist in the settings table
     * Dynamically adds columns if they don't exist (matching PHP behavior)
     * Columns are added in order: current_users (after bonus_reward if exists), then goal_users (after current_users)
     */
    private function ensureSettingsColumnsExist(array $columns = [])
    {
        if (empty($columns) || !Schema::hasTable('settings')) {
            return;
        }

        $columnDefinitions = [
            'current_users' => ['type' => 'integer', 'params' => [], 'default' => 99000, 'after' => 'bonus_reward'],
            'goal_users' => ['type' => 'integer', 'params' => [], 'default' => 1000000, 'after' => 'current_users'],
        ];

        // Process columns in order to handle dependencies (current_users must exist before goal_users)
        $orderedColumns = ['current_users', 'goal_users'];
        foreach ($orderedColumns as $column) {
            if (!in_array($column, $columns)) {
                continue; // Skip if not requested
            }

            if (!Schema::hasColumn('settings', $column)) {
                $def = $columnDefinitions[$column] ?? null;
                
                if ($def) {
                    $after = $def['after'] ?? null;
                    $afterColumnExists = $after ? Schema::hasColumn('settings', $after) : false;
                    
                    Schema::table('settings', function (Blueprint $table) use ($column, $def, $after, $afterColumnExists) {
                        $method = $def['type'];
                        $params = $def['params'] ?? [];

                        if ($method === 'integer') {
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
}


