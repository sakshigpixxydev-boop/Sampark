<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectToDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $roleName = $user->role->rolename ?? null;
            
            // Redirect to appropriate dashboard based on role
            switch ($roleName) {
                case 'Admin':
                    return redirect()->route('admin.dashboard');
                case 'Initiator':
                    return redirect()->route('initiator.dashboard');
                case 'School':
                    return redirect()->route('school.dashboard');
                case 'Accountant':
                    return redirect()->route('admin.dashboard');
                case 'Student':
                    return redirect()->route('student.dashboard');
                default:
                    if ($user->canAccessModule(10051)) {
                        return redirect()->route('admin.dashboard');
                    }
                    return redirect()->route('dashboard');
            }
        }
        
        return $next($request);
    }
}
