<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Allowed MIME types for CSV files
     */
    private const CSV_MIME_TYPES = [
        'text/csv',
        'text/plain',
        'application/csv',
        'text/comma-separated-values',
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    ];

    /**
     * Allowed MIME types for images
     */
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    /**
     * Maximum file size in bytes (10MB for CSV, 2MB for images)
     */
    private const MAX_CSV_SIZE = 10485760; // 10MB
    private const MAX_IMAGE_SIZE = 2097152; // 2MB

    /**
     * Validate and sanitize CSV/Excel file upload
     */
    public function validateCsvFile(UploadedFile $file): array
    {
        $errors = [];
        Log::info('FileUploadService: Validating file', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);

        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            $error = $file->getErrorMessage();
            Log::error('FileUploadService: File invalid', ['error' => $error]);
            $errors[] = 'File upload failed: ' . $error;
            return $errors;
        }

        // Check file size
        if ($file->getSize() > self::MAX_CSV_SIZE) {
            Log::warning('FileUploadService: File too large');
            $errors[] = 'File size exceeds maximum allowed size of 10MB.';
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::CSV_MIME_TYPES)) {
            Log::warning('FileUploadService: Invalid MIME type: ' . $file->getMimeType());
            $errors[] = 'Invalid file type. Only CSV and Excel files are allowed.';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])) {
            Log::warning('FileUploadService: Invalid extension: ' . $extension);
            $errors[] = 'Invalid file extension. Only .csv, .xls, and .xlsx files are allowed.';
        }

        // Check for null bytes and malicious content (only for text-based files)
        if (in_array($extension, ['csv', 'txt'])) {
            $content = file_get_contents($file->getRealPath());
            
            if (strpos($content, "\0") !== false) {
                Log::warning('FileUploadService: Null bytes found');
                $errors[] = 'File contains invalid characters.';
            }

            // Scan for malicious content patterns
            if ($this->containsMaliciousContent($content)) {
                Log::warning('FileUploadService: Malicious content detected');
                $errors[] = 'File contains potentially malicious content.';
            }
        }

        return $errors;
    }

    /**
     * Validate and sanitize image file upload
     */
    public function validateImageFile(UploadedFile $file): array
    {
        $errors = [];

        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            $errors[] = 'File upload failed. Please try again.';
            return $errors;
        }

        // Check file size
        if ($file->getSize() > self::MAX_IMAGE_SIZE) {
            $errors[] = 'Image size exceeds maximum allowed size of 2MB.';
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::IMAGE_MIME_TYPES)) {
            $errors[] = 'Invalid file type. Only JPEG, JPG, PNG, and GIF images are allowed.';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = 'Invalid file extension. Only .jpg, .jpeg, .png, and .gif files are allowed.';
        }

        // Verify it's actually an image
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            $errors[] = 'File is not a valid image.';
        }

        return $errors;
    }

    /**
     * Check for malicious content in file
     */
    private function containsMaliciousContent(string $content): bool
    {
        // Check for script tags and other dangerous patterns
        $dangerousPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store uploaded file securely
     */
    public function storeFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store file in private storage
        $path = $file->storeAs($directory, $filename, 'private');

        return $path;
    }

    /**
     * Store uploaded file to public disk (for documents that need to be viewable via URL)
     */
    public function storePublicFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Store uploaded image securely
     */
    public function storeImage(UploadedFile $file, string $directory = 'images'): string
    {
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store file in public storage
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $path, string $disk = 'private'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Sanitize filename
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove any path information
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Limit length
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 250);
            $filename = $basename . '.' . $extension;
        }

        return $filename;
    }
}
