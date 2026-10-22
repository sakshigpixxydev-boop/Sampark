<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared-secret guard for the public intake endpoints that an external site
 * posts into. The key is read from config so it can differ per environment.
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next, string $configKey = 'services.enquiry_api.key'): Response
    {
        $expected = config($configKey);

        if (empty($expected)) {
            Log::error('VerifyApiKey: no API key configured.', ['config_key' => $configKey]);

            return response()->json([
                'success' => false,
                'message' => 'API access is not configured.',
            ], 503);
        }

        $provided = $request->header('X-API-KEY') ?: $request->bearerToken();

        if (!is_string($provided) || !hash_equals((string) $expected, $provided)) {
            Log::warning('VerifyApiKey: rejected request with invalid API key.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
