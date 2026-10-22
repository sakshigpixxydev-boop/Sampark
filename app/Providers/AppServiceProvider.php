<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Custom validation rule for DD/MM/YYYY date format
        Validator::extend('date_format_dmy', function ($attribute, $value, $parameters, $validator) {
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return false;
            }
            $parts = explode('/', $value);
            return checkdate((int)$parts[1], (int)$parts[0], (int)$parts[2]);
        });

        Validator::replacer('date_format_dmy', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be in DD/MM/YYYY format.');
        });
    }
}
