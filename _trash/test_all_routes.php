<?php

/**
 * Comprehensive Route Testing Script
 * Tests all routes for Admin, Teacher, and Student roles
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Assignment;

echo "\n=== ROUTE TESTING SCRIPT ===\n";
echo "Testing all routes for each role\n";
echo "================================\n\n";

// Get all registered routes
$routes = Route::getRoutes();

// Categorize routes by role
$adminRoutes = [];
$teacherRoutes = [];
$studentRoutes = [];
$publicRoutes = [];

foreach ($routes as $route) {
    $name = $route->getName();
    $uri = $route->uri();
    $methods = implode('|', $route->methods());
    
    if (!$name) continue; // Skip unnamed routes
    
    $routeInfo = [
        'name' => $name,
        'uri' => $uri,
        'methods' => $methods,
        'middleware' => $route->middleware()
    ];
    
    if (str_starts_with($name, 'admin.')) {
        $adminRoutes[] = $routeInfo;
    } elseif (str_starts_with($name, 'teacher.')) {
        $teacherRoutes[] = $routeInfo;
    } elseif (str_starts_with($name, 'student.')) {
        $studentRoutes[] = $routeInfo;
    } else {
        $publicRoutes[] = $routeInfo;
    }
}

// Display statistics
echo "📊 ROUTE STATISTICS:\n";
echo "-------------------\n";
echo "Admin Routes: " . count($adminRoutes) . "\n";
echo "Teacher Routes: " . count($teacherRoutes) . "\n";
echo "Student Routes: " . count($studentRoutes) . "\n";
echo "Public Routes: " . count($publicRoutes) . "\n";
echo "Total Named Routes: " . (count($adminRoutes) + count($teacherRoutes) + count($studentRoutes) + count($publicRoutes)) . "\n\n";

// Function to display routes
function displayRoutes($routes, $title) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "  $title\n";
    echo str_repeat("=", 80) . "\n\n";
    
    if (empty($routes)) {
        echo "  No routes found.\n";
        return;
    }
    
    foreach ($routes as $route) {
        echo "✓ {$route['name']}\n";
        echo "  URI: {$route['uri']}\n";
        echo "  Methods: {$route['methods']}\n";
        
        if (!empty($route['middleware'])) {
            echo "  Middleware: " . implode(', ', $route['middleware']) . "\n";
        }
        echo "\n";
    }
}

// Display all routes by category
displayRoutes($adminRoutes, "ADMIN ROUTES");
displayRoutes($teacherRoutes, "TEACHER ROUTES");
displayRoutes($studentRoutes, "STUDENT ROUTES");

// Check for specific assignment routes
echo "\n" . str_repeat("=", 80) . "\n";
echo "  ASSIGNMENT ROUTE VERIFICATION\n";
echo str_repeat("=", 80) . "\n\n";

$assignmentRoutes = [
    'student.assignments.overview',
    'student.courses.modules.subpages.assignments.index',
    'student.courses.modules.subpages.assignments.show',
    'student.courses.modules.subpages.assignments.submit',
    'student.courses.modules.subpages.assignments.store-submission',
    'teacher.courses.modules.subpages.assignments.index',
    'teacher.courses.modules.subpages.assignments.show',
    'teacher.courses.modules.subpages.assignments.create',
    'teacher.courses.modules.subpages.assignments.store',
    'teacher.courses.modules.subpages.assignments.edit',
    'teacher.courses.modules.subpages.assignments.update',
    'teacher.courses.modules.subpages.assignments.destroy',
];

foreach ($assignmentRoutes as $routeName) {
    if (Route::has($routeName)) {
        $route = Route::getRoutes()->getByName($routeName);
        echo "✅ $routeName\n";
        echo "   URI: {$route->uri()}\n";
        echo "   Methods: " . implode('|', $route->methods()) . "\n\n";
    } else {
        echo "❌ $routeName - NOT FOUND\n\n";
    }
}

// Check for potential route conflicts
echo "\n" . str_repeat("=", 80) . "\n";
echo "  ROUTE CONFLICT CHECK\n";
echo str_repeat("=", 80) . "\n\n";

$conflicts = [];
$uriMap = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = $route->methods();
    
    foreach ($methods as $method) {
        $key = "$method:$uri";
        if (isset($uriMap[$key])) {
            $conflicts[] = [
                'uri' => $uri,
                'method' => $method,
                'routes' => [$uriMap[$key], $route->getName()]
            ];
        } else {
            $uriMap[$key] = $route->getName();
        }
    }
}

if (empty($conflicts)) {
    echo "✅ No route conflicts detected!\n";
} else {
    echo "⚠️  Found " . count($conflicts) . " potential conflicts:\n\n";
    foreach ($conflicts as $conflict) {
        echo "  URI: {$conflict['uri']}\n";
        echo "  Method: {$conflict['method']}\n";
        echo "  Conflicting routes: " . implode(', ', $conflict['routes']) . "\n\n";
    }
}

// Test route generation with sample data
echo "\n" . str_repeat("=", 80) . "\n";
echo "  ROUTE GENERATION TEST\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // Try to generate some common routes
    $testRoutes = [
        ['name' => 'student.dashboard', 'params' => []],
        ['name' => 'teacher.dashboard', 'params' => []],
        ['name' => 'admin.dashboard', 'params' => []],
        ['name' => 'login', 'params' => []],
    ];
    
    foreach ($testRoutes as $test) {
        try {
            $url = route($test['name'], $test['params']);
            echo "✅ {$test['name']}: $url\n";
        } catch (\Exception $e) {
            echo "❌ {$test['name']}: {$e->getMessage()}\n";
        }
    }
    
    // Test assignment routes with dummy IDs
    echo "\n--- Assignment Routes (with dummy IDs) ---\n\n";
    
    $assignmentTestRoutes = [
        ['name' => 'student.assignments.overview', 'params' => []],
        ['name' => 'student.courses.modules.subpages.assignments.index', 'params' => [1, 1, 1]],
        ['name' => 'student.courses.modules.subpages.assignments.show', 'params' => [1, 1, 1, 1]],
        ['name' => 'teacher.courses.modules.subpages.assignments.index', 'params' => [1, 1, 1]],
        ['name' => 'teacher.courses.modules.subpages.assignments.show', 'params' => [1, 1, 1, 1]],
    ];
    
    foreach ($assignmentTestRoutes as $test) {
        try {
            $url = route($test['name'], $test['params']);
            echo "✅ {$test['name']}\n   URL: $url\n";
        } catch (\Exception $e) {
            echo "❌ {$test['name']}: {$e->getMessage()}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error during route generation test: {$e->getMessage()}\n";
}

// Summary
echo "\n" . str_repeat("=", 80) . "\n";
echo "  SUMMARY\n";
echo str_repeat("=", 80) . "\n\n";

echo "✓ Route testing complete!\n";
echo "✓ Total routes analyzed: " . count($routes) . "\n";
echo "✓ Check the output above for any issues\n\n";

echo "💡 RECOMMENDATIONS:\n";
echo "-------------------\n";
echo "1. All assignment routes should use the full path format:\n";
echo "   student.courses.modules.subpages.assignments.show\n";
echo "2. Ensure all views use the correct route names\n";
echo "3. Clear route cache after any route changes: php artisan route:clear\n";
echo "4. Test routes in browser with actual data\n\n";

echo "=== END OF ROUTE TESTING ===\n\n";
