<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Get user's role and permissions
        if ($user->role) {
            // Support multiple permission keys separated by '|' or ','
            $keys = array_filter(array_map('trim', preg_split('/[|,]/', $permissionKey)));

            $query = $user->role->permissions();
            if (count($keys) > 1) {
                $hasPermission = $query->whereIn('permkey', $keys)->exists();
            } else {
                $hasPermission = $query->where('permkey', $permissionKey)->exists();
            }
            
            if ($hasPermission) {
                return $next($request);
            }
        }

        // User doesn't have permission
        abort(403);
    }
}
