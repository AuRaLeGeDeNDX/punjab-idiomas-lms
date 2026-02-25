<?php

namespace App\Console\Commands;

use App\Services\RedisConfigurationService;
use Illuminate\Console\Command;

class RedisSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:setup {--test : Test Redis connection and performance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up and test Redis configuration for the Institute LMS';

    protected RedisConfigurationService $redisService;

    public function __construct(RedisConfigurationService $redisService)
    {
        parent::__construct();
        $this->redisService = $redisService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Institute LMS Redis Setup');
        $this->info('========================');

        if ($this->option('test')) {
            $this->testRedis();
            return;
        }

        $this->setupRedis();
    }

    /**
     * Set up Redis configuration
     */
    private function setupRedis(): void
    {
        $this->info('Checking Redis availability...');
        
        if ($this->redisService->isRedisAvailable()) {
            $this->info('✅ Redis is available and connected');
            
            $health = $this->redisService->getRedisHealth();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Redis Version', $health['version']],
                    ['Memory Usage', $health['memory_usage']],
                    ['Connected Clients', $health['connected_clients']],
                    ['Uptime (seconds)', $health['uptime']],
                ]
            );

            if ($this->confirm('Would you like to configure the application to use Redis?')) {
                $this->redisService->configureRedis();
                $this->info('✅ Redis configuration applied');
                
                $this->info('');
                $this->info('To make this permanent, update your .env file:');
                $this->line('SESSION_DRIVER=redis');
                $this->line('CACHE_STORE=redis');
                $this->line('QUEUE_CONNECTION=redis');
            }
        } else {
            $this->error('❌ Redis is not available');
            $this->info('');
            $this->info('To install Redis:');
            $this->line('Windows: choco install redis-64');
            $this->line('macOS: brew install redis && brew services start redis');
            $this->line('Linux: sudo apt install redis-server');
            $this->info('');
            $this->info('The application will use database fallback for sessions and caching.');
            
            $this->redisService->configureRedis(); // This will set up fallback
        }
    }

    /**
     * Test Redis performance and functionality
     */
    private function testRedis(): void
    {
        $this->info('Testing Redis connection and performance...');
        
        if (!$this->redisService->isRedisAvailable()) {
            $this->error('❌ Redis is not available for testing');
            return;
        }

        $this->info('✅ Redis connection successful');
        
        // Run benchmark
        $this->info('Running performance benchmark...');
        $benchmark = $this->redisService->benchmarkRedis();
        
        if ($benchmark['available']) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Write Time (100 ops)', $benchmark['write_time'] . ' ms'],
                    ['Read Time (100 ops)', $benchmark['read_time'] . ' ms'],
                    ['Operations/Second', number_format($benchmark['operations_per_second'])],
                ]
            );
            
            // Performance assessment
            $opsPerSecond = $benchmark['operations_per_second'];
            if ($opsPerSecond > 10000) {
                $this->info('🚀 Excellent Redis performance!');
            } elseif ($opsPerSecond > 5000) {
                $this->info('✅ Good Redis performance');
            } else {
                $this->warn('⚠️  Redis performance may be limited');
            }
        } else {
            $this->error('❌ Benchmark failed');
        }

        // Test cache operations
        $this->info('Testing cache operations...');
        try {
            cache()->put('redis_test', 'Hello Redis!', 60);
            $value = cache()->get('redis_test');
            
            if ($value === 'Hello Redis!') {
                $this->info('✅ Cache operations working correctly');
                cache()->forget('redis_test');
            } else {
                $this->error('❌ Cache operations failed');
            }
        } catch (\Exception $e) {
            $this->error('❌ Cache test failed: ' . $e->getMessage());
        }
    }
}
