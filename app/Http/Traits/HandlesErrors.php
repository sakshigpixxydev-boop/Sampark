<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

trait HandlesErrors
{
    /**
     * Handle exceptions and return appropriate response
     */
    protected function handleError(\Exception $e, string $message = 'An error occurred', string $redirectRoute = null): RedirectResponse|JsonResponse
    {
        // Log the error
        Log::error($message . ': ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ]);

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        // Return redirect with error message and old input
        $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();
        
        return $redirect
            ->withInput()
            ->with('error', $message . (config('app.debug') ? ': ' . $e->getMessage() : ''));
    }

    /**
     * Handle success response
     */
    protected function handleSuccess(string $message, string $redirectRoute = null, array $data = []): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        }

        $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();
        return $redirect->with('success', $message);
    }
}
