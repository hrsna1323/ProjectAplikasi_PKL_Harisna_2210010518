<?php

namespace App\Helpers;

class SanitizationHelper
{
    /**
     * Sanitize a string for safe storage and display.
     */
    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Strip HTML tags
        $value = strip_tags($value);
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }

    /**
     * Sanitize a URL.
     */
    public static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        // Trim whitespace
        $url = trim($url);
        
        // Remove javascript: and data: protocols (XSS prevention)
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            return '';
        }
        
        // Encode special characters in URL
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        return $url;
    }

    /**
     * Sanitize an email address.
     */
    public static function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        return $email;
    }

    /**
     * Sanitize an integer value.
     */
    public static function sanitizeInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize a filename.
     */
    public static function sanitizeFilename(?string $filename): ?string
    {
        if ($filename === null) {
            return null;
        }

        // Remove path traversal characters
        $filename = str_replace(['../', '..\\', '/', '\\'], '', $filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        return $filename;
    }

    /**
     * Check if a string contains potential XSS patterns.
     */
    public static function containsXss(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<form/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string contains potential SQL injection patterns.
     */
    public static function containsSqlInjection(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE|TRUNCATE)\b)/i',
            '/(--)/',
            '/(\/\*.*\*\/)/',
            '/(\bOR\b\s+\d+\s*=\s*\d+)/i',
            '/(\bAND\b\s+\d+\s*=\s*\d+)/i',
            '/(\'|\"|;|`)/i',
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
