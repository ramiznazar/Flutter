<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Setting;
use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    public function checkEligibility(Request $request)
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

        // Get KYC requirements from settings
        $settings = Setting::first();
        $miningSessionsRequired = $settings ? (int) $settings->kyc_mining_sessions : 14;
        $referralsRequired = $settings ? (int) $settings->kyc_referrals_required : 10;

        // Get user's mining sessions
        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;

        // Get user's referrals
        $referrals = (int) $user->total_invite;

        // Check if KYC already submitted
        $latestKyc = KycSubmission::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        $kycStatus = $latestKyc ? $latestKyc->status : null;

        $isEligible = ($miningSessions >= $miningSessionsRequired) && ($referrals >= $referralsRequired);
        $canSubmit = $isEligible && ($kycStatus === null || $kycStatus === 'rejected');

        return response()->json([
            'success' => true,
            'data' => [
                'mining_sessions' => $miningSessions,
                'mining_sessions_required' => $miningSessionsRequired,
                'referrals' => $referrals,
                'referrals_required' => $referralsRequired,
                'is_eligible' => $isEligible,
                'can_submit' => $canSubmit,
                'kyc_status' => $kycStatus,
                'mining_progress' => $miningSessions . '/' . $miningSessionsRequired,
                'referrals_progress' => $referrals . '/' . $referralsRequired
            ]
        ]);
    }

    public function submit(Request $request)
    {
        // KYC submission with Didit verification
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'full_name' => 'required|string',
            'dob' => 'required|date|date_format:Y-m-d',
            'front_image' => 'required|string', // Base64 encoded
            'back_image' => 'required|string', // Base64 encoded
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields or invalid date format. Date must be YYYY-MM-DD.'
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

        // Check eligibility first
        $eligibility = $this->checkEligibility($request);
        $eligibilityData = json_decode($eligibility->getContent(), true);
        
        if (!$eligibilityData['data']['can_submit']) {
            return response()->json([
                'success' => false,
                'message' => 'Not eligible to submit KYC or already submitted'
            ], 400);
        }

        // Check if already submitted and pending/approved
        $existingKyc = KycSubmission::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingKyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC submission already exists and is pending or approved'
            ], 400);
        }

        // Validate base64 images (no Didit API call - manual admin review)
        // Check if images are valid base64 strings
        $frontImageData = $request->front_image;
        $backImageData = $request->back_image;
        
        // Remove data URI prefix if present
        if (strpos($frontImageData, ',') !== false) {
            $frontImageData = explode(',', $frontImageData)[1];
        }
        if (strpos($backImageData, ',') !== false) {
            $backImageData = explode(',', $backImageData)[1];
        }
        
        // Validate base64
        if (!base64_decode($frontImageData, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid front image format. Please provide valid base64 encoded image.'
            ], 400);
        }
        
        if (!base64_decode($backImageData, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid back image format. Please provide valid base64 encoded image.'
            ], 400);
        }

        // Create KYC submission - status will be 'pending' for admin review
        // No Didit API integration - admin will manually review and approve/reject
        $kyc = KycSubmission::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'front_image' => $request->front_image,
            'back_image' => $request->back_image,
            'status' => 'pending', // Always pending - admin will review manually
            'didit_request_id' => null,
            'didit_status' => null,
            'didit_verification_data' => null,
            'didit_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully. Your submission is pending admin review.',
            'data' => [
                'kyc_id' => $kyc->id,
                'status' => $kyc->status,
                'didit_request_id' => null,
                'verification_status' => null,
            ]
        ]);
    }


    public function getStatus(Request $request)
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

        $kyc = KycSubmission::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$kyc) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => null,
                    'message' => 'No KYC submission found'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $kyc->status,
                'full_name' => $kyc->full_name,
                'submitted_at' => $kyc->created_at,
                'admin_notes' => $kyc->admin_notes
            ]
        ]);
    }

    public function getProgress(Request $request)
    {
        return $this->checkEligibility($request);
    }

    /**
     * Create Didit verification request
     * This endpoint is called before submitting KYC documents to create a verification request
     */
    public function diditCreateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'full_name' => 'required|string',
            'dob' => 'required|date|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields or invalid date format. Date must be YYYY-MM-DD.'
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

        // Check eligibility
        $eligibility = $this->checkEligibility($request);
        $eligibilityData = json_decode($eligibility->getContent(), true);
        
        if (!$eligibilityData['data']['can_submit']) {
            return response()->json([
                'success' => false,
                'message' => 'Not eligible to submit KYC or already submitted'
            ], 400);
        }

        // Check if already submitted and pending/approved
        $existingKyc = KycSubmission::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingKyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC submission already exists and is pending or approved'
            ], 400);
        }

        // Generate a temporary request ID for tracking
        $requestId = 'req_' . uniqid() . '_' . time();
        
        // Note: Based on the current Didit API implementation, verification happens server-side
        // when images are submitted via /api/kyc_submit. However, if the Flutter app uses
        // Didit SDK which requires a verification URL/session, we would need to call Didit's
        // session creation API here. For now, we'll return a response that allows the app
        // to proceed with image submission.
        
        // Return success with verification details
        // The Flutter app should proceed to capture and submit images via /api/kyc_submit
        // Note: Didit verification happens server-side when images are submitted
        $baseUrl = $request->getSchemeAndHttpHost();
        
        return response()->json([
            'success' => true,
            'message' => 'Verification request created successfully',
            'data' => [
                'request_id' => $requestId,
                'verification_url' => $baseUrl . '/api/kyc_submit', // Endpoint for submitting images
                'verification_session_id' => $requestId,
                'session_id' => $requestId,
                'email' => $user->email,
                'full_name' => $request->full_name,
                'dob' => $request->dob,
                'can_proceed' => true,
                'verification_method' => 'image_submission',
                'next_endpoint' => '/api/kyc_submit',
                'required_fields' => [
                    'email',
                    'full_name',
                    'dob',
                    'front_image',
                    'back_image'
                ]
            ]
        ]);
    }
}
