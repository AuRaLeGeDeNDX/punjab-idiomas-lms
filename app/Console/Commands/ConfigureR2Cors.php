<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConfigureR2Cors extends Command
{
    protected $signature = 'r2:cors';
    protected $description = 'Set CORS policy on the Cloudflare R2 bucket';

    public function handle()
    {
        try {
            $bucket = config('filesystems.disks.r2.bucket');
            $endpoint = config('filesystems.disks.r2.endpoint');
            $key = config('filesystems.disks.r2.key');
            $secret = config('filesystems.disks.r2.secret');

            if (!$endpoint || !$key || !$secret) {
                $this->error("Missing R2 credentials in config/filesystems.php");
                return;
            }

            $client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1', // R2 requires us-east-1 natively usually, or auto
                'endpoint' => $endpoint,
                'credentials' => [
                    'key'    => $key,
                    'secret' => $secret,
                ],
            ]);
            
            $this->info("Setting CORS on bucket: {$bucket}");

            $client->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => [
                    'CORSRules' => [
                        [
                            'AllowedHeaders' => ['*', 'Content-Type', 'Authorization', 'X-Amz-User-Agent', 'X-Amz-Date'],
                            'AllowedMethods' => ['GET', 'PUT', 'POST', 'DELETE', 'HEAD'],
                            'AllowedOrigins' => ['*', 'https://punjabidiomas.com', 'https://www.punjabidiomas.com', 'http://localhost', 'http://localhost:8000'],
                            'ExposeHeaders' => ['ETag'],
                            'MaxAgeSeconds' => 3000,
                        ],
                    ],
                ],
            ]);

            $this->info("CORS policy successfully set for bucket {$bucket}!");
        } catch (\Exception $e) {
            $this->error("Failed to set CORS policy: " . $e->getMessage());
        }
    }
}
