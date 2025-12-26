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
        // Get user's spin information
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $spinInfo = \App\Models\SpinCailmed::where('UserID', $user->id)->first();
        $spinSetting = \App\Models\SpinSetting::first();

        return response()->json([
            'success' => true,
            'data' => [
                'spin_info' => $spinInfo,
                'spin_setting' => $spinSetting
            ]
        ]);
    }
}
