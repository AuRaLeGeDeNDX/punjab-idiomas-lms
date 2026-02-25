<?php
try {
    $u = \App\Models\User::first();
    if (!$u) die("No user found\n");
    \Illuminate\Support\Facades\Auth::login($u);
    
    $s = \App\Models\Subpage::first();
    if (!$s) die("No subpage found\n");
    
    $data = [
        'type' => 'text',
        'section' => 'main_content',
        'content' => null,
        'title' => 'Debug Creation',
        'description' => null,
        'visibility' => 'student',
        'is_active' => true,
    ];
    
    echo "Attempting to create content block...\n";
    
    // Simulate what the controller does
    $service = app(\App\Services\ContentBlockService::class);
    $content = $service->createContentBlock($s, $data);
    
    echo "SUCCESS: Content created with ID " . $content->id . "\n";
    
} catch (\Throwable $e) {
    echo "\nCRITICAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
