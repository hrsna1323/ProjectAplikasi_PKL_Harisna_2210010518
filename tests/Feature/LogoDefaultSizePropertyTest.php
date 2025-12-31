<?php

/**
 * Property-Based Test for Logo Component Default Size
 * 
 * Feature: implementasi-logo-aplikasi, Property 3: Default Size Fallback
 * Validates: Requirements 2.3
 * 
 * Property: For any invocation without a size parameter, the logo component 
 * should render with default dimensions of 40x40 pixels.
 */

use Illuminate\Support\Facades\Blade;

describe('Logo Default Size Property Tests', function () {
    
    it('uses default size of 40 when no size parameter is provided', function () {
        // Run multiple times to ensure consistency
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Render without size parameter
            $rendered = Blade::render('<x-logo />');
            
            // Use regex to match width and height with flexible whitespace
            expect($rendered)->toMatch('/width\s*=\s*"40"/', 
                "Iteration {$i}: Missing default width=40");
            expect($rendered)->toMatch('/height\s*=\s*"40"/', 
                "Iteration {$i}: Missing default height=40");
        }
    });
    
    it('falls back to default size when invalid size is provided', function () {
        // Test various invalid inputs
        $invalidSizes = [
            null,
            '',
            'invalid',
            'abc',
            -10,
            -50,
            0,
            'not-a-number',
            [],
        ];
        
        foreach ($invalidSizes as $invalidSize) {
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $invalidSize]);
            
            // Should fallback to default 40x40
            expect($rendered)->toMatch('/width\s*=\s*"40"/', 
                "Failed with invalid size: " . json_encode($invalidSize));
            expect($rendered)->toMatch('/height\s*=\s*"40"/', 
                "Failed with invalid size: " . json_encode($invalidSize));
        }
    });
    
    it('falls back to default when negative size is provided', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random negative size
            $negativeSize = -1 * rand(1, 100);
            
            $rendered = Blade::render('<x-logo :size="$size" />', ['size' => $negativeSize]);
            
            // Should fallback to 40
            expect($rendered)->toMatch('/width\s*=\s*"40"/', 
                "Failed with negative size={$negativeSize}");
            expect($rendered)->toMatch('/height\s*=\s*"40"/', 
                "Failed with negative size={$negativeSize}");
        }
    });
    
    it('falls back to default when zero size is provided', function () {
        $iterations = 20;
        
        for ($i = 0; $i < $iterations; $i++) {
            $rendered = Blade::render('<x-logo :size="0" />');
            
            expect($rendered)->toContain('width="40"');
            expect($rendered)->toContain('height="40"');
        }
    });
    
    it('uses default size when size parameter is explicitly null', function () {
        $iterations = 30;
        
        for ($i = 0; $i < $iterations; $i++) {
            $rendered = Blade::render('<x-logo :size="null" />');
            
            expect($rendered)->toContain('width="40"');
            expect($rendered)->toContain('height="40"');
        }
    });
    
    it('uses default size when size parameter is empty string', function () {
        $iterations = 30;
        
        for ($i = 0; $i < $iterations; $i++) {
            $rendered = Blade::render('<x-logo size="" />');
            
            expect($rendered)->toContain('width="40"');
            expect($rendered)->toContain('height="40"');
        }
    });
    
    it('maintains all other SVG properties when using default size', function () {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $rendered = Blade::render('<x-logo />');
            
            // Verify all essential properties are present
            expect($rendered)->toContain('viewBox="0 0 100 100"');
            expect($rendered)->toContain('width="40"');
            expect($rendered)->toContain('height="40"');
            
            // Should still have 4 rect elements
            $rectCount = substr_count($rendered, '<rect');
            expect($rectCount)->toBe(4);
            
            // Should still use currentColor
            $currentColorCount = substr_count($rendered, 'fill="currentColor"');
            expect($currentColorCount)->toBe(4);
        }
    });
});
