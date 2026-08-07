<?php

namespace App\Http\Controllers\Api\V1;

use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function live(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ], 'Application is alive.');
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
        ];

        if ($this->shouldCheckRedis()) {
            $checks['redis'] = $this->checkRedis();
        }

        if ($this->shouldCheckCache()) {
            $checks['cache'] = $this->checkCache();
        }

        $healthy = collect($checks)->every(fn (array $check) => $check['status'] === 'ok');

        $response = ApiResponse::success([
            'status' => $healthy ? 'ready' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 'Application is ready.' : 'Application is degraded.');

        if (! $healthy) {
            $response->setStatusCode(503);
        }

        return $response;
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (\Throwable) {
            return ['status' => 'error', 'message' => 'Database unavailable.'];
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();

            return ['status' => 'ok'];
        } catch (\Throwable) {
            return ['status' => 'error', 'message' => 'Redis unavailable.'];
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkCache(): array
    {
        try {
            Cache::store()->put('health_check', 'ok', 5);

            return ['status' => 'ok'];
        } catch (\Throwable) {
            return ['status' => 'error', 'message' => 'Cache unavailable.'];
        }
    }

    private function shouldCheckRedis(): bool
    {
        return config('cache.default') === 'redis'
            || config('queue.default') === 'redis'
            || config('session.driver') === 'redis';
    }

    private function shouldCheckCache(): bool
    {
        return config('cache.default') !== 'array';
    }
}
