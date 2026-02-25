<?php

use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\ContentBlockController;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

try {
    // 1. Setup Environment
    $u = User::first();
    Auth::login($u);
    $s = Subpage::first();
    
    // 2. Mock Request
    $request = Request::create(
        '/api/v1/dummy', 
        'POST', 
        [
            'type' => 'text',
            'section' => 'main_content',
            'title' => 'Debug Controller',
            'visibility' => 'student',
            // 'content' => null // Simulate frontend sending null/empty
        ]
    );
    
    // 3. Instantiate Controller
    $controller = app(ContentBlockController::class);
    
    echo "Controller instantiated.\n";
    
    // 4. Test Validation (Private method using Reflection)
    echo "Testing Validation...\n";
    $reflection = new ReflectionClass(ContentBlockController::class);
    $method = $reflection->getMethod('validateContentBlockRequest');
    $method->setAccessible(true);
    
    $correlationId = Str::uuid()->toString();
    $validated = $method->invokeArgs($controller, [$request, null, $correlationId]);
    
    echo "Validation Passed.\n";
    print_r($validated);
    
    // 5. Test Service Call (Simulating store method)
    echo "Testing Service Creation...\n";
    $content = app(\App\Services\ContentBlockService::class)->createContentBlock($s, $validated);
    echo "Service Creation Passed. ID: " . $content->id . "\n";
    
    // 5b. Test Logger (Private Property Access)
    echo "Testing Logger...\n";
    $loggerProperty = $reflection->getProperty('fileUploadLogger');
    $loggerProperty->setAccessible(true);
    $loggerInstance = $loggerProperty->getValue($controller);
    
    $loggerInstance->logUploadSuccess($correlationId, $content, ['test' => 'debug_script']);
    echo "Logger Passed.\n";

    // 6. Test Format (Private method)
    echo "Testing Formatting...\n";
    $formatMethod = $reflection->getMethod('formatContentBlock');
    $formatMethod->setAccessible(true);
    $formatted = $formatMethod->invokeArgs($controller, [$content]);
    
    echo "Formatting Passed.\n";
    
} catch (\Throwable $e) {
    echo "\nCRITICAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
