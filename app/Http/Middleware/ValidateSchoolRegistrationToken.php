<?php

namespace App\Http\Middleware;

use App\Models\SchoolLead;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSchoolRegistrationToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Invalid registration link.');
        }

        $lead = SchoolLead::where('registration_token', $token)->first();

        if (!$lead) {
            return redirect()->route('login')->with('error', 'Invalid registration link.');
        }

        if (!$lead->isTokenValid()) {
            return view('school-registration.expired-token', compact('lead'));
        }

        // Attach lead to request for use in controller
        $request->merge(['lead' => $lead]);

        return $next($request);
    }
}
