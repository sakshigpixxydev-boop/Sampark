<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
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
                // If no specific role, show a generic dashboard
                return view('dashboard');
        }
    }
}
