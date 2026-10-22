<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Custom Blade directive for permission checking
        Blade::if('canAccess', function ($permkey) {
            return auth()->check() && auth()->user()->canAccessModule($permkey);
        });
    }
}
