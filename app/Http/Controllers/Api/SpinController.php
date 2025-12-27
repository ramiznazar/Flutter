<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spin;
use Illuminate\Http\Request;

class SpinController extends Controller
{
    public function spin(Request $request)
    {
        $spins = Spin::where('Status', 1)->get();

        return response()->json($spins);
    }

    public function spinClaim(Request $request)
    {
        // Handle spin reward claiming
        // This would involve checking spin_cailmed table and updating user balance
        return response()->json([
            'success' => true,
            'message' => 'Spin claimed'
        ]);
    }

    public function getMySpinInfo(Request $request)
    {
        // Handle both Email (capital E) from FormData and email (lowercase) from JSON
        $email = $request->input('Email') ?? $request->input('email');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $spinSetting = \App\Models\SpinSetting::first();
        
        if (!$spinSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Spin settings not found'
            ], 404);
        }

        // Get or create spin_cailmed record
        $spinInfo = \App\Models\SpinCailmed::where('UserID', $user->id)->first();
        
        $currentTime = now();
        $startedAt = $currentTime->format('Y-m-d-H:i:s');
        $endAt = $currentTime->copy()->addSeconds((int) $spinSetting->Time)->format('Y-m-d-H:i:s');
        
        if (!$spinInfo) {
            // Create new record
            $spinInfo = \App\Models\SpinCailmed::create([
                'UserID' => $user->id,
                'Total' => '1',
                'EndAt' => $endAt,
                'StartedAt' => $startedAt
            ]);
        } else {
            // Check if time expired
            $endAtTime = \Carbon\Carbon::createFromFormat('Y-m-d-H:i:s', $spinInfo->EndAt);
            
            if ($currentTime->gt($endAtTime)) {
                // Reset the spin
                $spinInfo->update([
                    'Total' => '1',
                    'EndAt' => $endAt,
                    'StartedAt' => $startedAt
                ]);
            }
            
            // Refresh to get latest data
            $spinInfo->refresh();
        }

        // Return response in the format expected by Flutter (matching original PHP)
        return response()->json([[
            'MaxLimit' => (string) ($spinSetting->MaxLimit ?? '0'),
            'Time' => (string) ($spinSetting->Time ?? '0'),
            'AdType' => (string) ($spinSetting->AdType ?? '0'),
            'ShowAd' => (string) ($spinSetting->ShowAd ?? '0'),
            'Total' => (string) ($spinInfo->Total ?? '0'),
            'EndAt' => $spinInfo->EndAt ?? '',
            'StartedAt' => $spinInfo->StartedAt ?? '',
            'CurrentTime' => $currentTime->format('Y-m-d-H:i:s'),
            'SpinShow' => (string) ($spinSetting->SpinShow ?? '0')
        ]]);
    }
}
