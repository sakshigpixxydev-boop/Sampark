<?php

namespace App\Http\Traits;

trait SanitizesInput
{
    /**
     * Sanitize string input by removing HTML tags and trimming whitespace
     */
    protected function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return trim(strip_tags($value));
    }

    /**
     * Sanitize array of strings
     */
    protected function sanitizeArray(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }
        
        return array_map(function ($value) {
            return is_string($value) ? $this->sanitizeString($value) : $value;
        }, $values);
    }

    /**
     * Sanitize email input
     */
    protected function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }
        
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize numeric input
     */
    protected function sanitizeNumeric(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Sanitize phone number (keep only digits)
     */
    protected function sanitizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
