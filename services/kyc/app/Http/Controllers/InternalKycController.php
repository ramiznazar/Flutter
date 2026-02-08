<?php

namespace App\Http\Controllers;

use App\Models\KycSubmission;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Internal KYC API. Request/response match monolith KycController.
 */
class InternalKycController extends Controller
{
    public function checkEligibility(Request $request): JsonResponse
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

        $settings = Setting::first();
        $miningSessionsRequired = $settings ? (int) $settings->kyc_mining_sessions : 14;
        $referralsRequired = $settings ? (int) $settings->kyc_referrals_required : 10;

        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $miningSessions = $userLevel ? (int) $userLevel->mining_session : 0;

        $referrals = (int) $user->total_invite;

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

    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'full_name' => 'required|string',
            'dob' => 'required|date|date_format:Y-m-d',
            'front_image' => 'required|string',
            'back_image' => 'required|string',
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

        $eligibility = $this->checkEligibility($request);
        $eligibilityData = json_decode($eligibility->getContent(), true);

        if (!$eligibilityData['data']['can_submit']) {
            return response()->json([
                'success' => false,
                'message' => 'Not eligible to submit KYC or already submitted'
            ], 400);
        }

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

        $frontImageData = $request->front_image;
        $backImageData = $request->back_image;

        if (strpos($frontImageData, ',') !== false) {
            $frontImageData = explode(',', $frontImageData)[1];
        }
        if (strpos($backImageData, ',') !== false) {
            $backImageData = explode(',', $backImageData)[1];
        }

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

        $kyc = KycSubmission::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'front_image' => $request->front_image,
            'back_image' => $request->back_image,
            'status' => 'pending',
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

    public function getStatus(Request $request): JsonResponse
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

    public function getProgress(Request $request): JsonResponse
    {
        return $this->checkEligibility($request);
    }

    public function diditCreateRequest(Request $request): JsonResponse
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

        $eligibility = $this->checkEligibility($request);
        $eligibilityData = json_decode($eligibility->getContent(), true);

        if (!$eligibilityData['data']['can_submit']) {
            return response()->json([
                'success' => false,
                'message' => 'Not eligible to submit KYC or already submitted'
            ], 400);
        }

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

        $requestId = 'req_' . uniqid() . '_' . time();

        $baseUrl = rtrim(env('GATEWAY_PUBLIC_URL', $request->getSchemeAndHttpHost()), '/');

        return response()->json([
            'success' => true,
            'message' => 'Verification request created successfully',
            'data' => [
                'request_id' => $requestId,
                'verification_url' => $baseUrl . '/api/kyc_submit',
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
