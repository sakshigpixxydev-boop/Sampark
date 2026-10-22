<?php

namespace App\Rules;

use App\Services\FileUploadService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureCsvFile implements ValidationRule
{
    protected FileUploadService $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file (CSV or Excel).');
            return;
        }

        $errors = $this->fileUploadService->validateCsvFile($value);

        if (!empty($errors)) {
            $fail(implode(' ', $errors));
        }
    }
}
