<?php

/**
 * Property-Based Test for Logo Component Size Parameter
 * 
 * Feature: implementasi-logo-aplikasi, Property 2: Size Parameter Scaling
 * Validates: Requirements 2.3
 * 
 * Property: For any valid size parameter value, the logo component should render 
 * with width and height attributes equal to the provided size value.
 */

use Illuminate\Support\Facades\Blade;

describe('Logo Size Parameter Property Tests', function () {
    
    it('renders with width and height matching the provided size parameter', function () {
        // Run property test with 100 random size values
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random size between 10 and 300
            $randomSize = rand(10, 300);
            
            // Render the logo component with random size
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // Check that width and height attributes match the size using regex
            expect($rendered)->toMatch('/width\s*=\s*"' . $randomSize . '"/', 
                "Failed with size={$randomSize}: Width attribute doesn't match");
            expect($rendered)->toMatch('/height\s*=\s*"' . $randomSize . '"/', 
                "Failed with size={$randomSize}: Height attribute doesn't match");
        }
    });
    
    it('correctly handles string size parameters', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random size as string
            $randomSize = (string) rand(15, 200);
            
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            expect($rendered)->toMatch('/width\s*=\s*"' . $randomSize . '"/');
            expect($rendered)->toMatch('/height\s*=\s*"' . $randomSize . '"/');
        }
    });
    
    it('correctly handles integer size parameters', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random size as integer
            $randomSize = rand(20, 250);
            
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            expect($rendered)->toMatch('/width\s*=\s*"' . $randomSize . '"/');
            expect($rendered)->toMatch('/height\s*=\s*"' . $randomSize . '"/');
        }
    });
    
    it('maintains square aspect ratio (width equals height) for all sizes', function () {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $randomSize = rand(10, 300);
            
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $randomSize]);
            
            // Extract width and height values using regex
            preg_match('/width="(\d+)"/', $rendered, $widthMatches);
            preg_match('/height="(\d+)"/', $rendered, $heightMatches);
            
            $width = $widthMatches[1] ?? null;
            $height = $heightMatches[1] ?? null;
            
            expect($width)->toBe($height, 
                "Failed with size={$randomSize}: Width ({$width}) doesn't equal height ({$height})");
        }
    });
    
    it('handles edge case small sizes correctly', function () {
        // Test very small sizes
        $smallSizes = [1, 5, 8, 10, 12];
        
        foreach ($smallSizes as $size) {
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $size]);
            
            expect($rendered)->toMatch('/width\s*=\s*"' . $size . '"/');
            expect($rendered)->toMatch('/height\s*=\s*"' . $size . '"/');
        }
    });
    
    it('handles edge case large sizes correctly', function () {
        // Test very large sizes
        $largeSizes = [200, 300, 500, 1000];
        
        foreach ($largeSizes as $size) {
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $size]);
            
            expect($rendered)->toMatch('/width\s*=\s*"' . $size . '"/');
            expect($rendered)->toMatch('/height\s*=\s*"' . $size . '"/');
        }
    });
});
