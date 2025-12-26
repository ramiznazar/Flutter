<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Giveaway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class GiveawayViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $editId = $request->get('edit_id');
        $editGiveaway = null;
        
        if ($editId) {
            $editGiveaway = Giveaway::find($editId);
        }

        $giveaways = Giveaway::orderBy('created_at', 'desc')->get();

        return view('admin.giveaway.index', compact('giveaways', 'editGiveaway'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'giveaway_title' => 'required|string|max:255',
            'redirect_link' => 'required|url',
            'giveaway_description' => 'nullable|string',
            'icon' => 'nullable|url',
            'reward' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'nullable|in:active,inactive',
        ]);

        $giveawayId = $request->input('giveaway_id');
        // Convert empty string to 0 for proper comparison
        $giveawayId = $giveawayId === '' || $giveawayId === null ? 0 : (int)$giveawayId;

        // Check if extra columns exist, if not add them (matching PHP behavior)
        // These columns are added by ALTER TABLE in SQL backup AFTER INSERT statements
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

        if ($giveawayId > 0) {
            $giveaway = Giveaway::findOrFail($giveawayId);
            $giveaway->update([
                'title' => $request->giveaway_title,
                'description' => $request->giveaway_description ?? '',
                'link' => $request->redirect_link,
                'icon' => $request->icon ?? 'https://img.icons8.com/color/48/000000/gift.png',
                'reward' => $request->reward ?? 0,
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
                'status' => $request->status ?? 'active',
            ]);
            $message = 'Giveaway updated successfully.';
        } else {
            Giveaway::create([
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
            $message = 'Giveaway created successfully.';
        }

        return redirect()->route('admin.giveaway.index')
            ->with('message', $message)
            ->with('messageType', 'success');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'giveaway_id' => 'required|integer|exists:giveaway,id',
        ]);

        Giveaway::findOrFail($request->giveaway_id)->delete();

        return redirect()->route('admin.giveaway.index')
            ->with('message', 'Giveaway deleted successfully.')
            ->with('messageType', 'success');
    }
}















