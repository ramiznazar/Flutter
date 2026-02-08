<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway proxy for task: when task service is enabled, forwards requests to the task microservice.
 * Otherwise delegates to the local TaskController. Public API contract unchanged for Flutter.
 */
class TaskProxyController extends Controller
{
    private TaskController $taskController;

    public function __construct(TaskController $taskController)
    {
        $this->taskController = $taskController;
    }

    public function taskStart(Request $request)
    {
        return $this->proxyOrLocal('POST', 'task_start', $request, fn () => $this->taskController->taskStart($request));
    }

    public function taskClaimReward(Request $request)
    {
        return $this->proxyOrLocal('POST', 'task_claim_reward', $request, fn () => $this->taskController->taskClaimReward($request));
    }

    public function trackTask(Request $request)
    {
        return $this->proxyOrLocal('POST', 'task_track', $request, fn () => $this->taskController->trackTask($request));
    }

    public function getDailyTasks(Request $request)
    {
        return $this->proxyOrLocal('POST', 'get_daily_tasks', $request, fn () => $this->taskController->getDailyTasks($request));
    }

    private function proxyOrLocal(string $method, string $internalPath, Request $request, callable $local): mixed
    {
        if (! config('services.task.enabled')) {
            return $local();
        }

        $baseUrl = rtrim((string) config('services.task.url'), '/');
        $url = $baseUrl . '/internal/' . $internalPath;
        $timeout = (int) config('services.task.timeout', 10);
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
        Log::warning('Task proxy received non-JSON or error', [
            'url' => $url,
            'status' => $status,
            'body' => $response->body(),
        ]);
        return response()->json(
            ['success' => false, 'message' => 'Task service unavailable'],
            $status >= 500 ? $status : 502
        );
    }
}
