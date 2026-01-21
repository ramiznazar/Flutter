<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BadgeViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index()
    {
        $badges = Badge::orderBy('id', 'asc')->get();
        return view('admin.badges.index', compact('badges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'badge_name' => 'required|string|max:255',
            'badges_icon' => 'nullable|url|max:500',
            'mining_sessions_required' => 'nullable|integer|min:0',
            'spin_wheel_required' => 'nullable|integer|min:0',
            'invite_friends_required' => 'nullable|integer|min:0',
            'crutox_in_wallet_required' => 'nullable|numeric|min:0',
            'social_media_task_completed' => 'nullable|boolean',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = [
            'badge_name' => $request->badge_name,
            'mining_sessions_required' => $request->mining_sessions_required ?: null,
            'spin_wheel_required' => $request->spin_wheel_required ?: null,
            'invite_friends_required' => $request->invite_friends_required ?: null,
            'crutox_in_wallet_required' => $request->crutox_in_wallet_required ?: null,
            'social_media_task_completed' => $request->social_media_task_completed ? 1 : null,
        ];

        // Handle icon upload
        if ($request->hasFile('icon_file')) {
            $iconPath = $request->file('icon_file')->store('badges', 'public');
            $data['badges_icon'] = asset('storage/' . $iconPath);
        } elseif ($request->badges_icon) {
            $data['badges_icon'] = $request->badges_icon;
        } else {
            $data['badges_icon'] = null;
        }

        Badge::create($data);

        return redirect()->route('admin.badges.index')
            ->with('message', 'Badge created successfully.')
            ->with('messageType', 'success');
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);

        $request->validate([
            'badge_name' => 'required|string|max:255',
            'badges_icon' => 'nullable|url|max:500',
            'mining_sessions_required' => 'nullable|integer|min:0',
            'spin_wheel_required' => 'nullable|integer|min:0',
            'invite_friends_required' => 'nullable|integer|min:0',
            'crutox_in_wallet_required' => 'nullable|numeric|min:0',
            'social_media_task_completed' => 'nullable|boolean',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = [
            'badge_name' => $request->badge_name,
            'mining_sessions_required' => $request->mining_sessions_required ?: null,
            'spin_wheel_required' => $request->spin_wheel_required ?: null,
            'invite_friends_required' => $request->invite_friends_required ?: null,
            'crutox_in_wallet_required' => $request->crutox_in_wallet_required ?: null,
            'social_media_task_completed' => $request->social_media_task_completed ? 1 : null,
        ];

        // Handle icon upload
        if ($request->hasFile('icon_file')) {
            // Delete old icon if it was uploaded
            if ($badge->badges_icon && strpos($badge->badges_icon, '/storage/badges/') !== false) {
                $oldPath = str_replace(asset('storage/'), '', $badge->badges_icon);
                Storage::disk('public')->delete($oldPath);
            }
            
            $iconPath = $request->file('icon_file')->store('badges', 'public');
            $data['badges_icon'] = asset('storage/' . $iconPath);
        } elseif ($request->badges_icon) {
            $data['badges_icon'] = $request->badges_icon;
        }
        // If neither is provided, keep existing icon

        $badge->update($data);

        return redirect()->route('admin.badges.index')
            ->with('message', 'Badge updated successfully.')
            ->with('messageType', 'success');
    }

    public function destroy($id)
    {
        $badge = Badge::findOrFail($id);
        
        // Delete icon file if it was uploaded
        if ($badge->badges_icon && strpos($badge->badges_icon, '/storage/badges/') !== false) {
            $oldPath = str_replace(asset('storage/'), '', $badge->badges_icon);
            Storage::disk('public')->delete($oldPath);
        }
        
        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('message', 'Badge deleted successfully.')
            ->with('messageType', 'success');
    }
}
