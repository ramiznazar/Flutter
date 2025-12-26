<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycManageController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->get('id');

        if ($id) {
            $kyc = KycSubmission::with('user')
                ->find($id);

            if (!$kyc) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC submission not found'
                ], 404);
            }

            $kycData = $kyc->toArray();
            $kycData['user_email'] = $kyc->user ? $kyc->user->email : null;

            return response()->json([
                'success' => true,
                'data' => $kycData
            ]);
        }

        $submissions = KycSubmission::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($submission) {
                $data = $submission->toArray();
                $data['user_email'] = $submission->user ? $submission->user->email : null;
                return $data;
            });

        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $kyc = KycSubmission::find($id);

        if (!$kyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC submission not found'
            ], 404);
        }

        $kyc->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes ?? $kyc->admin_notes,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KYC status updated successfully'
        ]);
    }
}
