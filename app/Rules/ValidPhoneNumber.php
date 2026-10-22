<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Phone number must be 10 digits
        if (!preg_match('/^\d{10}$/', $value)) {
            $fail('The :attribute must be exactly 10 digits.');
        }
    }
}
