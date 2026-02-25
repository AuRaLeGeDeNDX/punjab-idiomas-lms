<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Content;

$contentId = $argv[1] ?? 7;

$content = Content::find($contentId);
if (!$content) {
    echo "Content not found!\n";
    exit(1);
}

$service = app(\App\Services\SecurePdfService::class);
$url = $service->generateSecureUrl($content, 10);

echo "Signed URL for Content ID {$contentId}:\n";
echo $url . "\n\n";

echo "Test with curl:\n";
echo "curl -I \"{$url}\"\n";
