<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeString implements ValidationRule
{
    /**
     * Run the validation rule.
     * Prevents XSS by checking for script tags and dangerous HTML
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check for script tags, event handlers, and other XSS vectors
        $dangerous_patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('The :attribute contains potentially dangerous content.');
                return;
            }
        }
    }
}
