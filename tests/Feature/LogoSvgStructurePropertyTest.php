<?php

/**
 * Property-Based Test for Logo Component SVG Structure
 * 
 * Feature: implementasi-logo-aplikasi, Property 1: SVG Structure Consistency
 * Validates: Requirements 1.3
 * 
 * Property: For any invocation of the logo component, the rendered SVG should 
 * always contain exactly 4 rect elements with consistent positioning and dimensions.
 */

use Illuminate\Support\Facades\Blade;

describe('Logo SVG Structure Property Tests', function () {
    
    it('always renders exactly 4 rect elements regardless of size parameter', function () {
        // Run property test with 100 random size values
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random size between 10 and 200
            $randomSize = rand(10, 200);
            
            // Render the logo component with random size
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // Count rect elements in the rendered output
            $rectCount = substr_count($rendered, '<rect');
            
            // Assert: Should always have exactly 4 rect elements
            expect($rectCount)->toBe(4, "Failed with size={$randomSize}: Expected 4 rect elements, got {$rectCount}");
        }
    });
    
    it('maintains consistent rect positioning across all invocations', function () {
        $iterations = 50;
        
        // Expected positions for the 4 boxes in 2x2 grid
        $expectedPositions = [
            ['x' => '10', 'y' => '10'], // Top-left
            ['x' => '55', 'y' => '10'], // Top-right
            ['x' => '10', 'y' => '55'], // Bottom-left
            ['x' => '55', 'y' => '55'], // Bottom-right
        ];
        
        for ($i = 0; $i < $iterations; $i++) {
            $randomSize = rand(20, 150);
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // Check each expected position exists in the output using regex
            foreach ($expectedPositions as $pos) {
                $positionPattern = '/x\s*=\s*"' . $pos['x'] . '"\s+y\s*=\s*"' . $pos['y'] . '"/';
                expect($rendered)->toMatch($positionPattern, 
                    "Failed with size={$randomSize}: Missing position x={$pos['x']}, y={$pos['y']}");
            }
        }
    });
    
    it('maintains consistent rect dimensions (35x35) across all invocations', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $randomSize = rand(15, 180);
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // All rects should have width="35" height="35"
            $widthCount = substr_count($rendered, 'width="35"');
            $heightCount = substr_count($rendered, 'height="35"');
            
            expect($widthCount)->toBe(4, "Failed with size={$randomSize}: Expected 4 rects with width=35");
            expect($heightCount)->toBe(4, "Failed with size={$randomSize}: Expected 4 rects with height=35");
        }
    });
    
    it('always uses viewBox="0 0 100 100" for scalability', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $randomSize = rand(10, 250);
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            expect($rendered)->toMatch('/viewBox\s*=\s*"0 0 100 100"/', 
                "Failed with size={$randomSize}: Missing or incorrect viewBox");
        }
    });
    
    it('always uses currentColor for fill to enable color inheritance', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $randomSize = rand(20, 160);
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // Count occurrences of fill="currentColor"
            $currentColorCount = substr_count($rendered, 'fill="currentColor"');
            
            expect($currentColorCount)->toBe(4, 
                "Failed with size={$randomSize}: Expected 4 rects with fill=currentColor, got {$currentColorCount}");
        }
    });
});
