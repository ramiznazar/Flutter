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

        // Convert base64 images to temporary files for Didit API
        $frontImagePath = $this->base64ToTempFile($request->front_image, 'kyc_front_');
        $backImagePath = $this->base64ToTempFile($request->back_image, 'kyc_back_');
        
        if (!$frontImagePath || !$backImagePath) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image format. Please provide valid base64 encoded images.'
            ], 400);
        }

        // Verify ID with Didit API (using email as vendor_data to match with user)
        $diditResult = $this->verifyWithDidit($frontImagePath, $backImagePath, $user->email);
        
        // Clean up temporary files
        @unlink($frontImagePath);
        @unlink($backImagePath);
        
        $diditRequestId = null;
        $diditStatus = null;
        $diditVerificationData = null;
        $diditVerifiedAt = null;
        $kycStatus = 'pending';
        
        if ($diditResult['success'] && isset($diditResult['data'])) {
            $diditData = $diditResult['data'];
            $diditRequestId = $diditData['request_id'] ?? null;
            
            if (isset($diditData['id_verification'])) {
                $idVerification = $diditData['id_verification'];
                $diditStatus = $idVerification['status'] ?? null;
                $diditVerificationData = json_encode($diditData, JSON_UNESCAPED_UNICODE);
                $diditVerifiedAt = now();
                
                // If Didit approves, set status to pending (admin can still review)
                // If Didit declines, set status to pending but admin will see the decline reason
                // Admin can override the status later
            }
        } else {
            // If Didit verification fails, still save the submission but mark it
            $diditVerificationData = json_encode([
                'error' => $diditResult['error'] ?? 'Verification failed',
                'details' => $diditResult['details'] ?? null
            ], JSON_UNESCAPED_UNICODE);
        }

        // Create KYC submission with Didit verification data
        $kyc = KycSubmission::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'front_image' => $request->front_image,
            'back_image' => $request->back_image,
            'status' => $kycStatus,
            'didit_request_id' => $diditRequestId,
            'didit_status' => $diditStatus,
            'didit_verification_data' => $diditVerificationData,
            'didit_verified_at' => $diditVerifiedAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => $diditResult['success'] ? 'KYC submitted and verified successfully.' : 'KYC submitted successfully. Verification pending.',
            'data' => [
                'kyc_id' => $kyc->id,
                'status' => $kyc->status,
                'didit_request_id' => $diditRequestId,
                'verification_status' => $diditStatus,
            ]
        ]);
    }

    /**
     * Convert base64 image to temporary file
     */
    private function base64ToTempFile($base64String, $prefix = 'kyc_')
    {
        // Remove data URI prefix if present
        if (strpos($base64String, ',') !== false) {
            $base64String = explode(',', $base64String)[1];
        }
        
        $imageData = base64_decode($base64String);
        if ($imageData === false) {
            return false;
        }
        
        $tempFile = tempnam(sys_get_temp_dir(), $prefix);
        file_put_contents($tempFile, $imageData);
        return $tempFile;
    }

    /**
     * Verify ID with Didit API
     */
    private function verifyWithDidit($frontImagePath, $backImagePath, $userEmail)
    {
        // Didit API Configuration
        $apiKey = '7wk_58gFnb27uqgApuMlEcpASwUurvX8IP6cKAZc4P4';
        $appId = 'ea69c49c-e8f0-4c64-aa9c-6a3cfa636232';
        $apiUrl = 'https://verification.didit.me/v2/id-verification/';
        
        try {
            // Create multipart form data using Guzzle HTTP client directly
            $client = new \GuzzleHttp\Client([
                'timeout' => 30.0,
                'verify' => true
            ]);
            
            $response = $client->request('POST', $apiUrl, [
                'headers' => [
                    'x-api-key' => $apiKey,
                    'accept' => 'application/json',
                ],
                'multipart' => [
                    [
                        'name' => 'front_image',
                        'contents' => fopen($frontImagePath, 'r'),
                        'filename' => 'front.jpg'
                    ],
                    [
                        'name' => 'back_image',
                        'contents' => fopen($backImagePath, 'r'),
                        'filename' => 'back.jpg'
                    ],
                    [
                        'name' => 'vendor_data',
                        'contents' => $userEmail
                    ],
                    [
                        'name' => 'app_id',
                        'contents' => $appId
                    ],
                    [
                        'name' => 'perform_document_liveness',
                        'contents' => 'true'
                    ],
                    [
                        'name' => 'expiration_date_not_detected_action',
                        'contents' => 'DECLINE'
                    ],
                    [
                        'name' => 'invalid_mrz_action',
                        'contents' => 'DECLINE'
                    ],
                    [
                        'name' => 'inconsistent_data_action',
                        'contents' => 'DECLINE'
                    ]
                ]
            ]);
            
            $responseBody = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'data' => $responseBody
            ];
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorResponse = $e->getResponse();
            $errorBody = $errorResponse ? json_decode($errorResponse->getBody()->getContents(), true) : null;
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'details' => $errorBody
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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
