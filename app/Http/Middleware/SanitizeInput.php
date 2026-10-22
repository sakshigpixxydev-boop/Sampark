<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only sanitize after CSRF verification passes
        // Get all input except protected fields
        $input = $request->except(['password', 'pin', 'password_confirmation', '_token']);
        
        array_walk_recursive($input, function (&$value, $key) {
            // Skip file uploads
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                return;
            }
            
            // Sanitize string values
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace(chr(0), '', $value);
                
                // Trim whitespace
                $value = trim($value);
            }
        });
        
        // Only merge sanitized input, keeping protected fields untouched
        $request->merge($input);
        
        return $next($request);
    }
}
