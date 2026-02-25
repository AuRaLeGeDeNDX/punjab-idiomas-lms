<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class RedisConfigurationService
{
    /**
     * Check if Redis is available and properly configured
     */
    public function isRedisAvailable(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            Log::warning('Redis connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure Redis settings based on availability
     */
    public function configureRedis(): void
    {
        if ($this->isRedisAvailable()) {
            $this->enableRedisConfiguration();
            Log::info('Redis configuration enabled successfully');
        } else {
            $this->enableFallbackConfiguration();
            Log::info('Using database fallback configuration (Redis unavailable)');
        }
    }

    /**
     * Enable Redis-based configuration
     */
    private function enableRedisConfiguration(): void
    {
        // Update runtime configuration to use Redis
        Config::set('session.driver', 'redis');
        Config::set('cache.default', 'redis');
        Config::set('queue.default', 'redis');
        
        // Configure Redis connections for different purposes
        Config::set('database.redis.sessions', [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
        ]);
        
        Config::set('database.redis.queues', [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
        ]);
    }

    /**
     * Enable database fallback configuration
     */
    private function enableFallbackConfiguration(): void
    {
        // Update runtime configuration to use database
        Config::set('session.driver', 'database');
        Config::set('cache.default', 'database');
        Config::set('queue.default', 'database');
    }

    /**
     * Get Redis health status
     */
    public function getRedisHealth(): array
    {
        $health = [
            'available' => false,
            'version' => null,
            'memory_usage' => null,
            'connected_clients' => null,
            'uptime' => null,
        ];

        try {
            if ($this->isRedisAvailable()) {
                $info = Redis::info();
                
                $health['available'] = true;
                $health['version'] = $info['redis_version'] ?? null;
                $health['memory_usage'] = $info['used_memory_human'] ?? null;
                $health['connected_clients'] = $info['connected_clients'] ?? null;
                $health['uptime'] = $info['uptime_in_seconds'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get Redis health info: ' . $e->getMessage());
        }

        return $health;
    }

    /**
     * Clear all Redis caches
     */
    public function clearRedisCache(): bool
    {
        try {
            if ($this->isRedisAvailable()) {
                Redis::flushdb();
                Log::info('Redis cache cleared successfully');
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear Redis cache: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Test Redis performance with a simple benchmark
     */
    public function benchmarkRedis(): array
    {
        $results = [
            'available' => false,
            'write_time' => null,
            'read_time' => null,
            'operations_per_second' => null,
        ];

        try {
            if (!$this->isRedisAvailable()) {
                return $results;
            }

            $results['available'] = true;
            $testKey = 'benchmark_test_' . time();
            $testValue = str_repeat('x', 1000); // 1KB test data
            $iterations = 100;

            // Benchmark write operations
            $startTime = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                Redis::set($testKey . '_' . $i, $testValue);
            }
            $writeTime = microtime(true) - $startTime;
            $results['write_time'] = round($writeTime * 1000, 2); // Convert to milliseconds

            // Benchmark read operations
            $startTime = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                Redis::get($testKey . '_' . $i);
            }
            $readTime = microtime(true) - $startTime;
            $results['read_time'] = round($readTime * 1000, 2); // Convert to milliseconds

            // Calculate operations per second
            $totalTime = $writeTime + $readTime;
            $totalOperations = $iterations * 2; // read + write
            $results['operations_per_second'] = round($totalOperations / $totalTime);

            // Clean up test keys
            for ($i = 0; $i < $iterations; $i++) {
                Redis::del($testKey . '_' . $i);
            }

        } catch (\Exception $e) {
            Log::error('Redis benchmark failed: ' . $e->getMessage());
        }

        return $results;
    }
}