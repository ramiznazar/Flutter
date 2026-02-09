<?php

namespace App\Services;

use App\Models\Level;
use App\Models\UserLevel;
use Illuminate\Support\Facades\Http;

/**
 * When mining service is enabled, fetches mining_session and spin_wheel from the mining microservice
 * and syncs them to local user_levels so getLevel, getBadges, and KYC show correct progress
 * even when the service is later unreachable or when microservices are turned off.
 */
class MiningStatsService
{
    /**
     * Get mining_session and spin_wheel for a user. When mining service is enabled, fetches from
     * the service and syncs to local user_levels; otherwise reads from local. Returns [mining_session, spin_wheel].
     */
    public static function getAndSyncMiningStats(int $userId, string $email): array
    {
        $default = ['mining_session' => 0, 'spin_wheel' => 0];

        if (config('services.mining.enabled')) {
            $baseUrl = rtrim((string) config('services.mining.url'), '/');
            $url = $baseUrl . '/internal/user_mining_stats';
            $timeout = (int) config('services.mining.timeout', 10);
            $secret = config('services.internal_api_secret');
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'X-Internal-Secret' => $secret,
                ])->timeout($timeout)->post($url, ['email' => $email]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['mining_session'])) {
                        $stats = [
                            'mining_session' => (int) $data['mining_session'],
                            'spin_wheel' => (int) ($data['spin_wheel'] ?? 0),
                        ];
                        self::syncToLocal($userId, $stats);
                        return $stats;
                    }
                }
            } catch (\Throwable $e) {
                // Fall through to local
            }
        }

        $userLevel = UserLevel::where('user_id', $userId)->select('mining_session', 'spin_wheel')->first();
        return [
            'mining_session' => $userLevel ? (int) $userLevel->mining_session : 0,
            'spin_wheel' => $userLevel ? (int) $userLevel->spin_wheel : 0,
        ];
    }

    /**
     * Sync mining_session and spin_wheel from mining service to local user_levels.
     */
    private static function syncToLocal(int $userId, array $stats): void
    {
        $userLevel = UserLevel::where('user_id', $userId)->first();
        if ($userLevel) {
            $userLevel->update([
                'mining_session' => $stats['mining_session'],
                'spin_wheel' => $stats['spin_wheel'],
            ]);
        } else {
            $firstLevel = Level::orderBy('id')->first();
            UserLevel::create([
                'user_id' => $userId,
                'mining_session' => $stats['mining_session'],
                'spin_wheel' => $stats['spin_wheel'],
                'current_level' => $firstLevel ? $firstLevel->id : 1,
                'achieved_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
