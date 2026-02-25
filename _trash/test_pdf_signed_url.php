<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$content = App\Models\Content::find(6);
$service = app(App\Services\SecurePdfService::class);
$signedUrl = $service->generateSecureUrl($content, 5);

echo "Signed URL for PDF stream:\n";
echo $signedUrl . "\n\n";

echo "Try accessing this URL directly in your browser:\n";
echo "It should download the PDF file.\n";
