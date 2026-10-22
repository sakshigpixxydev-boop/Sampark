<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('agreements:check-expiry')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'sanitize' => \App\Http\Middleware\SanitizeInput::class,
            'api.key' => \App\Http\Middleware\VerifyApiKey::class,
        ]);

        // Machine-to-machine endpoints: no session, no CSRF token to present.
        // NOTE: app/Http/Middleware/VerifyCsrfToken.php is dead code (never
        // registered), so razorpay/webhook was not actually exempt until now.
        $middleware->validateCsrfTokens(except: [
            'api/enquiries',
            'razorpay/webhook',
        ]);
        
        // Apply sanitization AFTER CSRF verification
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // Set priority to ensure CSRF runs before sanitization
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            return back()->withInput()->with('error', 'The uploaded file is too large. Please upload smaller image.');
        });
    })->create();

