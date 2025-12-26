<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Giveaway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GiveawayManageController extends Controller
{
    public function index()
    {
        // Check if extra columns exist, if not add them (matching PHP behavior)
        $this->ensureGiveawayColumnsExist();

        $giveaways = Giveaway::orderBy('created_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'reward' => $item->reward ?? 0,
                    'redirect_link' => $item->link,
                    'start_date' => $item->start_date ?? '',
                    'end_date' => $item->end_date ?? '',
                    'status' => $item->status ?? 'active',
                    'icon' => $item->icon ?? ''
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $giveaways
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'giveaway_title' => 'required|string',
            'redirect_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Title and redirect link are required.'
            ], 400);
        }

        // Check if extra columns exist, if not add them (matching PHP behavior)
        $this->ensureGiveawayColumnsExist();

        $giveaway = Giveaway::create([
            'title' => $request->giveaway_title,
            'description' => $request->giveaway_description ?? '',
            'link' => $request->redirect_link,
            'icon' => $request->icon ?? 'https://img.icons8.com/color/48/000000/gift.png',
            'reward' => $request->reward ?? 0,
            'start_date' => $request->start_date ?? null,
            'end_date' => $request->end_date ?? null,
            'status' => $request->status ?? 'active',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Giveaway created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'giveaway_title' => 'required|string',
            'redirect_link' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID, title, and redirect link are required.'
            ], 400);
        }

        $giveaway = Giveaway::find($id);

        if (!$giveaway) {
            return response()->json([
                'success' => false,
                'message' => 'Giveaway not found'
            ], 404);
        }

        // Check if extra columns exist, if not add them (matching PHP behavior)
        $this->ensureGiveawayColumnsExist();

        $updateData = [
            'title' => $request->giveaway_title,
            'description' => $request->giveaway_description ?? $giveaway->description,
            'link' => $request->redirect_link,
            'icon' => $request->icon ?? $giveaway->icon,
        ];

        // Only update extra columns if they exist
        if (Schema::hasColumn('giveaway', 'reward')) {
            $updateData['reward'] = $request->reward ?? $giveaway->reward;
        }
        if (Schema::hasColumn('giveaway', 'start_date')) {
            $updateData['start_date'] = $request->start_date ?? $giveaway->start_date;
        }
        if (Schema::hasColumn('giveaway', 'end_date')) {
            $updateData['end_date'] = $request->end_date ?? $giveaway->end_date;
        }
        if (Schema::hasColumn('giveaway', 'status')) {
            $updateData['status'] = $request->status ?? $giveaway->status;
        }

        $giveaway->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Giveaway updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $giveaway = Giveaway::find($id);

        if (!$giveaway) {
            return response()->json([
                'success' => false,
                'message' => 'Giveaway not found'
            ], 404);
        }

        $giveaway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway deleted successfully.'
        ]);
    }

    /**
     * Ensure giveaway table has all required columns
     * These columns are added by ALTER TABLE in SQL backup AFTER INSERT statements
     */
    private function ensureGiveawayColumnsExist()
    {
        if (!Schema::hasColumn('giveaway', 'reward')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->decimal('reward', 10, 2)->default(0)->nullable()->after('description');
            });
        }
        if (!Schema::hasColumn('giveaway', 'start_date')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->dateTime('start_date')->nullable()->after('reward');
            });
        }
        if (!Schema::hasColumn('giveaway', 'end_date')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->dateTime('end_date')->nullable()->after('start_date');
            });
        }
        if (!Schema::hasColumn('giveaway', 'status')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->string('status', 50)->default('active')->nullable()->after('end_date');
            });
        }
        if (!Schema::hasColumn('giveaway', 'redirect_link')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->text('redirect_link')->nullable()->after('link');
            });
        }
    }
}
