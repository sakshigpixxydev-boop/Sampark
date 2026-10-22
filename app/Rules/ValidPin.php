<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPin implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // PIN must be exactly 4 digits
        if (!preg_match('/^\d{4}$/', $value)) {
            $fail('The :attribute must be exactly 4 digits.');
        }
    }
}
