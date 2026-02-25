<?php

namespace Tests\Traits;

use Closure;
use Exception;

trait PropertyTesting
{
    /**
     * Run a property-based test with multiple iterations
     *
     * @param Closure $test The test closure to run
     * @param int $iterations Number of iterations to run (default: 100)
     * @param string|null $propertyDescription Description of the property being tested
     * @throws Exception
     */
    protected function propertyTest(Closure $test, int $iterations = 10, ?string $propertyDescription = null): void
    {
        $failures = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            try {
                // Run the test
                $test();
                
            } catch (Exception $e) {
                $failures[] = [
                    'iteration' => $i + 1,
                    'exception' => $e,
                    'message' => $e->getMessage(),
                ];
                
                // Stop on first failure for debugging
                throw new Exception(
                    sprintf(
                        "Property test failed on iteration %d/%d%s: %s",
                        $i + 1,
                        $iterations,
                        $propertyDescription ? " ({$propertyDescription})" : "",
                        $e->getMessage()
                    ),
                    0,
                    $e
                );
            }
        }
        
        if (!empty($failures)) {
            $failureCount = count($failures);
            $failureMessages = array_map(fn($f) => "Iteration {$f['iteration']}: {$f['message']}", $failures);
            
            throw new Exception(
                sprintf(
                    "Property test failed %d/%d times%s:\n%s",
                    $failureCount,
                    $iterations,
                    $propertyDescription ? " ({$propertyDescription})" : "",
                    implode("\n", $failureMessages)
                )
            );
        }
    }
}