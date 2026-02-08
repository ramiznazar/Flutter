<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway proxy for gamification: when gamification service is enabled, forwards requests to the gamification microservice.
 * Otherwise delegates to local MysteryBoxController, BoosterController, AdBoosterController. Public API unchanged for Flutter.
 */
class GamificationProxyController extends Controller
{
    private MysteryBoxController $mysteryBoxController;
    private BoosterController $boosterController;
    private AdBoosterController $adBoosterController;

    public function __construct(
        MysteryBoxController $mysteryBoxController,
        BoosterController $boosterController,
        AdBoosterController $adBoosterController
    ) {
        $this->mysteryBoxController = $mysteryBoxController;
        $this->boosterController = $boosterController;
        $this->adBoosterController = $adBoosterController;
    }

    public function mysteryBoxWatchAd(Request $request)
    {
        return $this->proxyOrLocal('mystery_box_watch_ad', $request, fn () => $this->mysteryBoxController->watchAd($request));
    }

    public function mysteryBoxClick(Request $request)
    {
        return $this->proxyOrLocal('mystery_box_click', $request, fn () => $this->mysteryBoxController->click($request));
    }

    public function mysteryBoxOpen(Request $request)
    {
        return $this->proxyOrLocal('mystery_box_open', $request, fn () => $this->mysteryBoxController->open($request));
    }

    public function mysteryBoxDetails(Request $request)
    {
        return $this->proxyOrLocal('mystery_box_details', $request, fn () => $this->mysteryBoxController->getDetails($request));
    }

    public function boosterStatus(Request $request)
    {
        return $this->proxyOrLocal('booster_status', $request, fn () => $this->boosterController->boosterStatus($request));
    }

    public function boosterClaim(Request $request)
    {
        return $this->proxyOrLocal('booster_claim', $request, fn () => $this->boosterController->boosterClaim($request));
    }

    public function adBoosterStatus(Request $request)
    {
        return $this->proxyOrLocal('ad_booster_status', $request, fn () => $this->adBoosterController->status($request));
    }

    public function adBoosterClaim(Request $request)
    {
        return $this->proxyOrLocal('ad_booster_claim', $request, fn () => $this->adBoosterController->claim($request));
    }

    private function proxyOrLocal(string $internalPath, Request $request, callable $local): mixed
    {
        if (! config('services.gamification.enabled')) {
            return $local();
        }

        $baseUrl = rtrim((string) config('services.gamification.url'), '/');
        $url = $baseUrl . '/internal/' . $internalPath;
        $timeout = (int) config('services.gamification.timeout', 10);
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
        Log::warning('Gamification proxy received non-JSON or error', [
            'url' => $url,
            'status' => $status,
            'body' => $response->body(),
        ]);
        return response()->json(
            ['success' => false, 'message' => 'Gamification service unavailable'],
            $status >= 500 ? $status : 502
        );
    }
}
