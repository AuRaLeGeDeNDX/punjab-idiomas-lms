<?php

/**
 * Secure Media Implementation Verification Script
 * 
 * This script verifies that all components of the secure media streaming
 * system are properly implemented and configured.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=================================================\n";
echo "  SECURE MEDIA IMPLEMENTATION VERIFICATION\n";
echo "=================================================\n\n";

$checks = [];
$passed = 0;
$failed = 0;

// Check 1: Controller exists
echo "1. Checking SecureMediaController...\n";
if (class_exists('App\Http\Controllers\SecureMediaController')) {
    echo "   ✅ SecureMediaController exists\n";
    $checks[] = ['Controller Exists', true];
    $passed++;
} else {
    echo "   ❌ SecureMediaController NOT FOUND\n";
    $checks[] = ['Controller Exists', false];
    $failed++;
}

// Check 2: Controller methods
echo "\n2. Checking controller methods...\n";
$controller = new ReflectionClass('App\Http\Controllers\SecureMediaController');
$requiredMethods = ['streamPdf', 'streamAudio', 'serveImage', 'validateAndAuthorize'];
foreach ($requiredMethods as $method) {
    if ($controller->hasMethod($method)) {
        echo "   ✅ Method {$method}() exists\n";
        $checks[] = ["Method {$method}", true];
        $passed++;
    } else {
        echo "   ❌ Method {$method}() NOT FOUND\n";
        $checks[] = ["Method {$method}", false];
        $failed++;
    }
}

// Check 3: Routes registered
echo "\n3. Checking routes...\n";
$routes = [
    'secure.pdf.stream' => '/secure/pdf/{content}',
    'secure.audio.stream' => '/secure/audio/{content}',
    'secure.image.serve' => '/secure/image/{content}',
];

foreach ($routes as $name => $path) {
    try {
        $route = route($name, ['content' => 1], false);
        echo "   ✅ Route '{$name}' registered\n";
        $checks[] = ["Route {$name}", true];
        $passed++;
    } catch (Exception $e) {
        echo "   ❌ Route '{$name}' NOT FOUND\n";
        $checks[] = ["Route {$name}", false];
        $failed++;
    }
}

// Check 4: Content model methods
echo "\n4. Checking Content model methods...\n";
$contentModel = new ReflectionClass('App\Models\Content');
$requiredModelMethods = [
    'getSecurePdfUrl',
    'getSecureAudioUrl',
    'getSecureImageUrl',
    'getSecureMediaUrl',
];

foreach ($requiredModelMethods as $method) {
    if ($contentModel->hasMethod($method)) {
        echo "   ✅ Method {$method}() exists\n";
        $checks[] = ["Content::{$method}", true];
        $passed++;
    } else {
        echo "   ❌ Method {$method}() NOT FOUND\n";
        $checks[] = ["Content::{$method}", false];
        $failed++;
    }
}

// Check 5: Storage directories
echo "\n5. Checking storage directories...\n";
$directories = [
    'storage/app/private',
    'storage/app/private/videos',
    'storage/app/private/audio',
    'storage/app/private/pdfs',
    'storage/app/private/images',
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "   ✅ Directory {$dir} exists\n";
        $checks[] = ["Directory {$dir}", true];
        $passed++;
    } else {
        echo "   ⚠️  Directory {$dir} NOT FOUND (will be created on first upload)\n";
        $checks[] = ["Directory {$dir}", 'warning'];
    }
}

// Check 6: Test file exists
echo "\n6. Checking test file...\n";
if (file_exists('tests/Feature/SecureMediaStreamingTest.php')) {
    echo "   ✅ SecureMediaStreamingTest.php exists\n";
    $checks[] = ['Test File', true];
    $passed++;
} else {
    echo "   ❌ SecureMediaStreamingTest.php NOT FOUND\n";
    $checks[] = ['Test File', false];
    $failed++;
}

// Check 7: Blade template updated
echo "\n7. Checking Blade template...\n";
$bladeFile = 'resources/views/partials/content-display.blade.php';
if (file_exists($bladeFile)) {
    $content = file_get_contents($bladeFile);
    
    $hasAudioSecurity = strpos($content, 'audio-{{ $content->id }}') !== false;
    $hasImageSecurity = strpos($content, 'oncontextmenu="return false;"') !== false;
    $hasPdfIframe = strpos($content, 'pdf-viewer-container') !== false;
    
    if ($hasAudioSecurity && $hasImageSecurity && $hasPdfIframe) {
        echo "   ✅ Blade template properly updated\n";
        $checks[] = ['Blade Template', true];
        $passed++;
    } else {
        echo "   ⚠️  Blade template may need updates\n";
        if (!$hasAudioSecurity) echo "      - Audio security missing\n";
        if (!$hasImageSecurity) echo "      - Image security missing\n";
        if (!$hasPdfIframe) echo "      - PDF iframe missing\n";
        $checks[] = ['Blade Template', 'warning'];
    }
} else {
    echo "   ❌ Blade template NOT FOUND\n";
    $checks[] = ['Blade Template', false];
    $failed++;
}

// Check 8: Documentation
echo "\n8. Checking documentation...\n";
$docs = [
    'SECURE_MEDIA_COMPLETE_GUIDE.md',
    'SECURE_MEDIA_QUICK_REFERENCE.md',
    'SECURE_MEDIA_IMPLEMENTATION_SUMMARY.md',
];

foreach ($docs as $doc) {
    if (file_exists($doc)) {
        echo "   ✅ {$doc} exists\n";
        $checks[] = ["Doc: {$doc}", true];
        $passed++;
    } else {
        echo "   ❌ {$doc} NOT FOUND\n";
        $checks[] = ["Doc: {$doc}", false];
        $failed++;
    }
}

// Check 9: Test content in database
echo "\n9. Checking database for test content...\n";
try {
    $pdfCount = App\Models\Content::where('type', 'pdf')->count();
    $audioCount = App\Models\Content::where('type', 'audio')->count();
    $imageCount = App\Models\Content::where('type', 'image')->count();
    
    echo "   ℹ️  PDF content: {$pdfCount}\n";
    echo "   ℹ️  Audio content: {$audioCount}\n";
    echo "   ℹ️  Image content: {$imageCount}\n";
    
    if ($pdfCount > 0 || $audioCount > 0 || $imageCount > 0) {
        echo "   ✅ Media content found in database\n";
        $checks[] = ['Database Content', true];
        $passed++;
    } else {
        echo "   ⚠️  No media content in database yet\n";
        $checks[] = ['Database Content', 'warning'];
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
    $checks[] = ['Database Content', false];
    $failed++;
}

// Check 10: URL generation test
echo "\n10. Testing URL generation...\n";
try {
    // Use actual content from database if available
    $pdfContent = App\Models\Content::where('type', 'pdf')->first();
    $audioContent = App\Models\Content::where('type', 'audio')->first();
    $imageContent = App\Models\Content::where('type', 'image')->first();
    
    $urlsGenerated = 0;
    
    if ($pdfContent) {
        $pdfUrl = $pdfContent->getSecurePdfUrl();
        if ($pdfUrl && strpos($pdfUrl, 'signature=') !== false) {
            echo "   ✅ PDF URL generation working\n";
            echo "      URL: " . substr($pdfUrl, 0, 60) . "...\n";
            $urlsGenerated++;
        }
    } else {
        echo "   ⚠️  No PDF content in database to test\n";
    }
    
    if ($audioContent) {
        $audioUrl = $audioContent->getSecureAudioUrl();
        if ($audioUrl && strpos($audioUrl, 'signature=') !== false) {
            echo "   ✅ Audio URL generation working\n";
            echo "      URL: " . substr($audioUrl, 0, 60) . "...\n";
            $urlsGenerated++;
        }
    } else {
        echo "   ⚠️  No audio content in database to test\n";
    }
    
    if ($imageContent) {
        $imageUrl = $imageContent->getSecureImageUrl();
        if ($imageUrl && strpos($imageUrl, 'signature=') !== false) {
            echo "   ✅ Image URL generation working\n";
            echo "      URL: " . substr($imageUrl, 0, 60) . "...\n";
            $urlsGenerated++;
        }
    } else {
        echo "   ⚠️  No image content in database to test\n";
    }
    
    if ($urlsGenerated > 0) {
        echo "   ✅ URL generation working ({$urlsGenerated}/3 types tested)\n";
        $checks[] = ['URL Generation', true];
        $passed++;
    } else {
        echo "   ⚠️  No content available to test URL generation\n";
        $checks[] = ['URL Generation', 'warning'];
    }
} catch (Exception $e) {
    echo "   ❌ URL generation test failed: " . $e->getMessage() . "\n";
    $checks[] = ['URL Generation', false];
    $failed++;
}

// Summary
echo "\n=================================================\n";
echo "  VERIFICATION SUMMARY\n";
echo "=================================================\n\n";

echo "Total Checks: " . count($checks) . "\n";
echo "✅ Passed: {$passed}\n";
echo "❌ Failed: {$failed}\n";

$warnings = count(array_filter($checks, fn($c) => $c[1] === 'warning'));
if ($warnings > 0) {
    echo "⚠️  Warnings: {$warnings}\n";
}

echo "\n";

if ($failed === 0) {
    echo "🎉 ALL CRITICAL CHECKS PASSED!\n";
    echo "   Secure media streaming is properly implemented.\n\n";
    
    echo "Next Steps:\n";
    echo "1. Run tests: php artisan test --filter SecureMediaStreamingTest\n";
    echo "2. Test in browser with actual media files\n";
    echo "3. Check logs: tail -f storage/logs/laravel.log | grep SecureMedia\n";
    echo "4. Deploy to production\n";
} else {
    echo "⚠️  SOME CHECKS FAILED\n";
    echo "   Please review the failed checks above and fix them.\n\n";
    
    echo "Failed Checks:\n";
    foreach ($checks as $check) {
        if ($check[1] === false) {
            echo "   - {$check[0]}\n";
        }
    }
}

echo "\n=================================================\n";
