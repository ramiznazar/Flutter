<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway proxy for KYC: when KYC service is enabled, forwards requests to the KYC microservice.
 * Otherwise delegates to local KycController. Public API unchanged for Flutter.
 */
class KycProxyController extends Controller
{
    public function __construct(
        private KycController $kycController
    ) {}

    public function checkEligibility(Request $request)
    {
        return $this->proxyOrLocal('kyc_check_eligibility', $request, fn () => $this->kycController->checkEligibility($request));
    }

    public function submit(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint only accepts POST requests. Please use POST method with required fields: email, full_name, dob, front_image, back_image.'
            ], 405);
        }
        return $this->proxyOrLocal('kyc_submit', $request, fn () => $this->kycController->submit($request));
    }

    public function getStatus(Request $request)
    {
        return $this->proxyOrLocal('kyc_get_status', $request, fn () => $this->kycController->getStatus($request));
    }

    public function getProgress(Request $request)
    {
        return $this->proxyOrLocal('get_kyc_progress', $request, fn () => $this->kycController->getProgress($request));
    }

    public function diditCreateRequest(Request $request)
    {
        return $this->proxyOrLocal('didit_create_request', $request, fn () => $this->kycController->diditCreateRequest($request));
    }

    private function proxyOrLocal(string $internalPath, Request $request, callable $local): mixed
    {
        if (! config('services.kyc.enabled')) {
            return $local();
        }

        $baseUrl = rtrim((string) config('services.kyc.url'), '/');
        $url = $baseUrl . '/internal/' . $internalPath;
        $timeout = (int) config('services.kyc.timeout', 10);
        $secret = config('services.internal_api_secret');

        $headers = [
            'Accept' => 'application/json',
            'X-Internal-Secret' => $secret,
        ];

        $attempt = function () use ($url, $request, $headers, $timeout) {
            return Http::withHeaders($headers)
                ->timeout($timeout)
                ->withBody($request->getContent(), 'application/json')
                ->post($url);
        };

        $response = $attempt();
        if ($response->failed() && $response->clientError() === false) {
            $response = $attempt();
        }

        if ($response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $status = $response->status();
        $body = $response->json();
        if (is_array($body)) {
            return response()->json($body, $status);
        }
        Log::warning('KYC proxy received non-JSON or error', [
            'url' => $url,
            'status' => $status,
            'body' => $response->body(),
        ]);
        return response()->json(
            ['success' => false, 'message' => 'KYC service unavailable'],
            $status >= 500 ? $status : 502
        );
    }
}
