<?php

use App\Services\ContentService;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Feature: manajemen-konten-skpd, Property 9: URL validation
 * 
 * *For any* URL input, the system should validate the URL format and reject malformed URLs.
 * 
 * **Validates: Requirements 3.3, 10.1**
 */
describe('Property 9: URL validation', function () {
    
    beforeEach(function () {
        $this->contentService = app(ContentService::class);
    });

    /**
     * Property test: For any valid URL with http/https scheme and valid host,
     * the validateUrl method should return true.
     */
    test('valid URLs with http/https scheme are accepted', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Use Faker's built-in url() which generates valid URLs
            $url = fake()->url();
            
            $result = $this->contentService->validateUrl($url);
            
            expect($result)->toBeTrue(
                "Valid URL '{$url}' should be accepted by validateUrl()"
            );
        }
    });

    /**
     * Property test: For any URL without a scheme or with invalid scheme,
     * the validateUrl method should return false.
     */
    test('URLs without valid http/https scheme are rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            $host = fake()->domainName();
            $path = fake()->optional(0.5)->regexify('/[a-z0-9]{1,20}');
            
            // Generate invalid scheme variations
            $invalidSchemes = [
                '',           // No scheme
                'ftp',        // FTP scheme
                'file',       // File scheme
                'mailto',     // Mailto scheme
                'javascript', // JavaScript scheme
                'data',       // Data scheme
                fake()->lexify('???'), // Random 3-letter scheme
            ];
            
            $invalidScheme = fake()->randomElement($invalidSchemes);
            
            if ($invalidScheme === '') {
                // URL without scheme
                $url = $host . ($path ?? '');
            } else {
                // URL with invalid scheme
                $url = $invalidScheme . '://' . $host . ($path ?? '');
            }
            
            $result = $this->contentService->validateUrl($url);
            
            expect($result)->toBeFalse(
                "URL '{$url}' with invalid/missing scheme should be rejected by validateUrl()"
            );
        }
    });

    /**
     * Property test: For any malformed URL string,
     * the validateUrl method should return false.
     */
    test('malformed URLs are rejected', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate various malformed URL patterns
            $malformedPatterns = [
                fake()->word(),                          // Just a word
                fake()->sentence(),                      // A sentence
                'http://',                               // Scheme only
                'https://',                              // Scheme only
                'http:///path',                          // Missing host
                'https:///path',                         // Missing host
                '://' . fake()->domainName(),            // Missing scheme
                fake()->numberBetween(1, 1000),          // Just a number
                fake()->email(),                         // Email address
                'http://' . fake()->word() . ' ' . fake()->word(), // URL with space
                fake()->regexify('[^a-zA-Z0-9]{5,20}'),  // Special characters only
            ];
            
            $malformedUrl = fake()->randomElement($malformedPatterns);
            
            // Convert to string if needed
            $malformedUrl = (string) $malformedUrl;
            
            $result = $this->contentService->validateUrl($malformedUrl);
            
            expect($result)->toBeFalse(
                "Malformed URL '{$malformedUrl}' should be rejected by validateUrl()"
            );
        }
    });

    /**
     * Property test: For any URL with valid format,
     * the validation result should be consistent across multiple calls.
     */
    test('URL validation is deterministic', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate a random URL (valid or invalid)
            $isValid = fake()->boolean();
            
            if ($isValid) {
                $url = fake()->url();
            } else {
                $url = fake()->randomElement([
                    fake()->word(),
                    'ftp://' . fake()->domainName(),
                    fake()->email(),
                ]);
            }
            
            // Call validateUrl multiple times
            $result1 = $this->contentService->validateUrl($url);
            $result2 = $this->contentService->validateUrl($url);
            $result3 = $this->contentService->validateUrl($url);
            
            // All results should be identical
            expect($result1)->toBe($result2,
                "URL validation should be deterministic for '{$url}'"
            );
            expect($result2)->toBe($result3,
                "URL validation should be deterministic for '{$url}'"
            );
        }
    });

    /**
     * Property test: For any valid http URL,
     * converting to https should also be valid.
     */
    test('valid http URLs remain valid when converted to https', function () {
        // Run 100 iterations as per design document requirements
        for ($i = 0; $i < 100; $i++) {
            // Generate a valid URL and test both http and https versions
            $url = fake()->url();
            
            // Extract components and rebuild with both schemes
            $parsed = parse_url($url);
            $host = $parsed['host'];
            $path = $parsed['path'] ?? '';
            
            $httpUrl = 'http://' . $host . $path;
            $httpsUrl = 'https://' . $host . $path;
            
            $httpResult = $this->contentService->validateUrl($httpUrl);
            $httpsResult = $this->contentService->validateUrl($httpsUrl);
            
            expect($httpResult)->toBeTrue(
                "Valid HTTP URL '{$httpUrl}' should be accepted"
            );
            expect($httpsResult)->toBeTrue(
                "Valid HTTPS URL '{$httpsUrl}' should be accepted"
            );
        }
    });
});
