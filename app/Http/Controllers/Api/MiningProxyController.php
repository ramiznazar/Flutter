<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway proxy for mining: when mining service is enabled, forwards requests to the mining microservice.
 * Otherwise delegates to the local MiningController. Public API contract unchanged for Flutter.
 */
class MiningProxyController extends Controller
{
    private MiningController $miningController;

    public function __construct(MiningController $miningController)
    {
        $this->miningController = $miningController;
    }

    public function startMining(Request $request)
    {
        return $this->proxyOrLocal('POST', 'start_mining', $request, fn () => $this->miningController->startMining($request));
    }

    public function miningStatus(Request $request)
    {
        return $this->proxyOrLocal('GET', 'mining_status', $request, fn () => $this->miningController->miningStatus($request));
    }

    public function startCoin(Request $request)
    {
        return $this->proxyOrLocal('POST', 'start_coin', $request, fn () => $this->miningController->startCoin($request));
    }

    public function claimBonus(Request $request)
    {
        return $this->proxyOrLocal('POST', 'claim_bonus', $request, fn () => $this->miningController->claimBonus($request));
    }

    public function bonusHistory(Request $request)
    {
        return $this->proxyOrLocal('POST', 'bonus_history', $request, fn () => $this->miningController->bonusHistory($request));
    }

    public function socialClaim(Request $request)
    {
        return $this->proxyOrLocal('POST', 'social_claim', $request, fn () => $this->miningController->socialClaim($request));
    }

    public function socialList(Request $request)
    {
        return $this->proxyOrLocal('POST', 'social_list', $request, fn () => $this->miningController->socialList($request));
    }

    public function addDailyReward(Request $request)
    {
        return $this->proxyOrLocal('POST', 'add_daily_reward', $request, fn () => $this->miningController->addDailyReward($request));
    }

    public function getDailyRewardStatus(Request $request)
    {
        return $this->proxyOrLocal('POST', 'get_daily_reward_status', $request, fn () => $this->miningController->getDailyRewardStatus($request));
    }

    /**
     * If mining service is enabled, forward to it (with one retry on failure). Otherwise run the local callback.
     */
    private function proxyOrLocal(string $method, string $internalPath, Request $request, callable $local): mixed
    {
        if (! config('services.mining.enabled')) {
            return $local();
        }

        $baseUrl = rtrim((string) config('services.mining.url'), '/');
        $url = $baseUrl . '/internal/' . $internalPath;
        $timeout = (int) config('services.mining.timeout', 10);
        $secret = config('services.internal_api_secret');

        $headers = [
            'Accept' => 'application/json',
            'X-Internal-Secret' => $secret,
        ];

        $attempt = function () use ($method, $url, $request, $headers, $timeout) {
            if (strtoupper($method) === 'GET') {
                return Http::withHeaders($headers)
                    ->timeout($timeout)
                    ->get($url, $request->query());
            }
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
        Log::warning('Mining proxy received non-JSON or error', [
            'url' => $url,
            'status' => $status,
            'body' => $response->body(),
        ]);
        return response()->json(
            ['success' => false, 'message' => 'Mining service unavailable'],
            $status >= 500 ? $status : 502
        );
    }
}
