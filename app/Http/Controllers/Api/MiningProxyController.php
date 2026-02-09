<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
     * On connection failure, timeout, or non-success response we fall back to local so the app never gets 502.
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

        try {
            $response = $attempt();
            if ($response->failed() && $response->clientError() === false) {
                $response = $attempt();
            }

            if ($response->successful()) {
                $json = $response->json();
                $this->syncBalanceToLocal($request, $internalPath, $json);
                return response()->json($json, $response->status());
            }

            $status = $response->status();
            $body = $response->json();
            if (is_array($body)) {
                return response()->json($body, $status);
            }
            Log::warning('Mining proxy received non-JSON or error, falling back to local', [
                'url' => $url,
                'path' => $internalPath,
                'status' => $status,
                'body' => substr((string) $response->body(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Mining proxy request failed, falling back to local', [
                'url' => $url,
                'path' => $internalPath,
                'message' => $e->getMessage(),
            ]);
        }

        return $local();
    }

    /**
     * When mining service is enabled, sync balance from proxy response to local user.token
     * so GET /api/admin/users_manage returns the correct balance (e.g. after daily reward on mining service).
     */
    private function syncBalanceToLocal(Request $request, string $internalPath, array $json): void
    {
        $balance = null;
        if (isset($json['balance']) && is_numeric($json['balance'])) {
            $balance = (float) $json['balance'];
        } elseif (isset($json['new_balance']) && is_numeric($json['new_balance'])) {
            $balance = (float) $json['new_balance'];
        }
        if ($balance === null) {
            return;
        }
        $email = $request->input('email') ?? $request->query('email');
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        User::where('email', $email)->update(['token' => $balance]);
    }
}
