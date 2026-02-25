<?php

/**
 * Secure Video Streaming Implementation Verification Script
 * 
 * This script verifies that all components of the secure video streaming
 * system are properly installed and configured.
 * 
 * Run: php verify_secure_video_implementation.php
 */

echo "=================================================\n";
echo "Secure Video Streaming - Implementation Verification\n";
echo "=================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Check if SecureVideoController exists
echo "1. Checking SecureVideoController...\n";
if (file_exists('app/Http/Controllers/SecureVideoController.php')) {
    $success[] = "✅ SecureVideoController exists";
    
    // Check if it has the stream method
    $content = file_get_contents('app/Http/Controllers/SecureVideoController.php');
    if (strpos($content, 'public function stream') !== false) {
        $success[] = "✅ SecureVideoController has stream() method";
    } else {
        $errors[] = "❌ SecureVideoController missing stream() method";
    }
    
    // Check for Range request support
    if (strpos($content, 'streamWithRange') !== false) {
        $success[] = "✅ HTTP Range request support implemented";
    } else {
        $warnings[] = "⚠️  HTTP Range request support may be missing";
    }
} else {
    $errors[] = "❌ SecureVideoController not found";
}

// 2. Check Content model
echo "\n2. Checking Content model...\n";
if (file_exists('app/Models/Content.php')) {
    $success[] = "✅ Content model exists";
    
    $content = file_get_contents('app/Models/Content.php');
    
    // Check for getSecureVideoUrl method
    if (strpos($content, 'public function getSecureVideoUrl') !== false) {
        $success[] = "✅ Content model has getSecureVideoUrl() method";
    } else {
        $errors[] = "❌ Content model missing getSecureVideoUrl() method";
    }
    
    // Check if getDisplayContent uses secure video URLs
    if (strpos($content, 'getSecureVideoUrl()') !== false) {
        $success[] = "✅ Content model uses secure video URLs";
    } else {
        $warnings[] = "⚠️  Content model may not use secure video URLs";
    }
} else {
    $errors[] = "❌ Content model not found";
}

// 3. Check routes
echo "\n3. Checking routes...\n";
if (file_exists('routes/web.php')) {
    $success[] = "✅ Routes file exists";
    
    $content = file_get_contents('routes/web.php');
    
    // Check for secure video route
    if (strpos($content, 'secure.video.stream') !== false) {
        $success[] = "✅ Secure video route registered";
    } else {
        $errors[] = "❌ Secure video route not found";
    }
    
    // Check for signed middleware
    if (strpos($content, "->middleware('signed')") !== false || 
        strpos($content, '->middleware("signed")') !== false) {
        $success[] = "✅ Signed middleware configured";
    } else {
        $errors[] = "❌ Signed middleware not configured";
    }
} else {
    $errors[] = "❌ Routes file not found";
}

// 4. Check filesystem configuration
echo "\n4. Checking filesystem configuration...\n";
if (file_exists('config/filesystems.php')) {
    $success[] = "✅ Filesystem config exists";
    
    $content = file_get_contents('config/filesystems.php');
    
    // Check for private disk
    if (strpos($content, "'private' =>") !== false) {
        $success[] = "✅ Private disk configured";
        
        // Check if it points to storage/app/private
        if (strpos($content, "storage_path('app/private')") !== false) {
            $success[] = "✅ Private disk points to correct location";
        } else {
            $warnings[] = "⚠️  Private disk may not point to storage/app/private";
        }
    } else {
        $errors[] = "❌ Private disk not configured";
    }
} else {
    $errors[] = "❌ Filesystem config not found";
}

// 5. Check storage directory
echo "\n5. Checking storage directory...\n";
if (is_dir('storage/app/private')) {
    $success[] = "✅ Private storage directory exists";
    
    if (is_dir('storage/app/private/videos')) {
        $success[] = "✅ Videos directory exists";
        
        // Check permissions
        if (is_writable('storage/app/private/videos')) {
            $success[] = "✅ Videos directory is writable";
        } else {
            $errors[] = "❌ Videos directory is not writable";
        }
    } else {
        $warnings[] = "⚠️  Videos directory not found (will be created on first upload)";
    }
} else {
    $errors[] = "❌ Private storage directory not found";
}

// 6. Check views
echo "\n6. Checking views...\n";
if (file_exists('resources/views/partials/content-display.blade.php')) {
    $success[] = "✅ Content display partial exists";
    
    $content = file_get_contents('resources/views/partials/content-display.blade.php');
    
    // Check for video case
    if (strpos($content, "@case('video')") !== false) {
        $success[] = "✅ Video display case exists";
        
        // Check for security features
        if (strpos($content, 'controlsList="nodownload"') !== false) {
            $success[] = "✅ Download prevention implemented";
        } else {
            $warnings[] = "⚠️  Download prevention may not be implemented";
        }
        
        if (strpos($content, 'oncontextmenu="return false;"') !== false) {
            $success[] = "✅ Right-click prevention implemented";
        } else {
            $warnings[] = "⚠️  Right-click prevention may not be implemented";
        }
    } else {
        $warnings[] = "⚠️  Video display case not found";
    }
} else {
    $warnings[] = "⚠️  Content display partial not found";
}

// 7. Check tests
echo "\n7. Checking tests...\n";
if (file_exists('tests/Feature/SecureVideoStreamingTest.php')) {
    $success[] = "✅ Test suite exists";
    
    $content = file_get_contents('tests/Feature/SecureVideoStreamingTest.php');
    
    // Count test methods
    $testCount = preg_match_all('/public function test_/', $content);
    if ($testCount > 0) {
        $success[] = "✅ Test suite contains $testCount test cases";
    }
} else {
    $warnings[] = "⚠️  Test suite not found";
}

// 8. Check documentation
echo "\n8. Checking documentation...\n";
$docs = [
    'SECURE_VIDEO_STREAMING_IMPLEMENTATION.md',
    'SECURE_VIDEO_STREAMING_COMPLETE.md',
    'SECURE_VIDEO_STREAMING_GUIDE.md',
    'SECURE_VIDEO_STREAMING_FINAL_SUMMARY.md',
];

foreach ($docs as $doc) {
    if (file_exists($doc)) {
        $success[] = "✅ Documentation: $doc";
    } else {
        $warnings[] = "⚠️  Documentation missing: $doc";
    }
}

// Print results
echo "\n=================================================\n";
echo "VERIFICATION RESULTS\n";
echo "=================================================\n\n";

if (!empty($success)) {
    echo "✅ SUCCESS (" . count($success) . " items):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . " items):\n";
    foreach ($warnings as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORS (" . count($errors) . " items):\n";
    foreach ($errors as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

// Final verdict
echo "=================================================\n";
if (empty($errors)) {
    echo "✅ VERIFICATION PASSED!\n";
    echo "\nThe secure video streaming system is properly installed.\n";
    echo "\nNext steps:\n";
    echo "1. Run tests: php artisan test --filter SecureVideoStreamingTest\n";
    echo "2. Upload a test video through the content builder\n";
    echo "3. Test video playback as different user roles\n";
    echo "4. Monitor logs: tail -f storage/logs/laravel.log | grep SecureVideo\n";
} else {
    echo "❌ VERIFICATION FAILED!\n";
    echo "\nPlease fix the errors above before using the system.\n";
    echo "\nFor help, see: SECURE_VIDEO_STREAMING_GUIDE.md\n";
}
echo "=================================================\n";

// Exit with appropriate code
exit(empty($errors) ? 0 : 1);
