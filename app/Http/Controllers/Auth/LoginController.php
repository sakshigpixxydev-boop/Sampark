<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SysUser;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login with password or PIN (auto-detected)
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'credential' => 'required|string',
        ]);

        // Find user by username, email, or phone
        $user = SysUser::where('username', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        // Check if user exists
        if (!$user) {
            \Log::warning('Login failed: User not found', ['identifier' => $request->identifier]);
            $this->auditLogger->logAuth('LOGIN_FAILED', null, 'Login failed: User not found', [
                'identifier' => $request->identifier,
                'reason' => 'user_not_found'
            ]);
            throw ValidationException::withMessages([
                'identifier' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is inactive
        if ($user->inactive) {
            \Log::warning('Login failed: User inactive', ['identifier' => $request->identifier]);
            $this->auditLogger->logAuth('LOGIN_FAILED', $user->sysuser_id, 'Login failed: User inactive', [
                'identifier' => $request->identifier,
                'reason' => 'user_inactive'
            ]);
            throw ValidationException::withMessages([
                'identifier' => ['Your account is inactive. Please contact the administrator.'],
            ]);
        }

        // Auto-detect credential type and authenticate
        $authenticated = false;
        $authMethod = null;

        // Check if credential is a 4-digit PIN
        if (preg_match('/^\d{4}$/', $request->credential)) {
            // Try PIN authentication first
            $authenticated = $user->checkPin($request->credential);
            $authMethod = 'pin';
            \Log::info('PIN authentication attempt', [
                'identifier' => $request->identifier,
                'success' => $authenticated
            ]);

            // If PIN fails, try as password (in case user has a 4-digit password)
            if (!$authenticated) {
                $authenticated = Hash::check($request->credential, $user->pswd);
                if ($authenticated) {
                    $authMethod = 'password';
                    \Log::info('Fallback password authentication successful', [
                        'identifier' => $request->identifier
                    ]);
                }
            }
        } else {
            // Try password authentication first
            $authenticated = Hash::check($request->credential, $user->pswd);
            $authMethod = 'password';
            \Log::info('Password authentication attempt', [
                'identifier' => $request->identifier,
                'success' => $authenticated
            ]);

            // If password fails, try mobile number as password (for students who forgot password)
            if (!$authenticated && preg_match('/^\d{10}$/', $request->credential)) {
                if ($user->phone === $request->credential) {
                    $authenticated = true;
                    $authMethod = 'mobile_as_password';
                    \Log::info('Mobile number as password authentication successful', [
                        'identifier' => $request->identifier
                    ]);
                }
            }

            // If still fails and credential is exactly 4 characters, try as PIN
            if (!$authenticated && strlen($request->credential) === 4) {
                $authenticated = $user->checkPin($request->credential);
                if ($authenticated) {
                    $authMethod = 'pin';
                    \Log::info('Fallback PIN authentication successful', [
                        'identifier' => $request->identifier
                    ]);
                }
            }
        }

        if (!$authenticated) {
            \Log::warning('Login failed: Invalid credentials', ['identifier' => $request->identifier]);
            $this->auditLogger->logAuth('LOGIN_FAILED', $user->sysuser_id, 'Login failed: Invalid credentials', [
                'identifier' => $request->identifier,
                'reason' => 'invalid_credentials'
            ]);
            throw ValidationException::withMessages([
                'credential' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log the user in
        Auth::login($user, $request->filled('remember'));
        \Log::info('User logged in successfully', [
            'identifier' => $request->identifier,
            'user_id' => $user->sysuser_id,
            'auth_method' => $authMethod
        ]);

        // Log successful login
        $this->auditLogger->logAuth('LOGIN_SUCCESS', $user->sysuser_id, 'User logged in successfully', [
            'identifier' => $request->identifier,
            'auth_method' => $authMethod
        ]);

        $request->session()->regenerate();

        // Redirect to role-specific dashboard
        return $this->redirectToDashboard($user);
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    protected function redirectToDashboard($user)
    {
        $roleName = $user->role->rolename ?? '';
        $roleLower = strtolower($roleName);

        if ($roleLower === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($roleLower === 'initiator') {
            return redirect()->route('initiator.dashboard');
        } elseif ($roleLower === 'school') {
            return redirect()->route('school.dashboard');
        } elseif ($roleLower === 'accountant') {
            return redirect()->route('admin.dashboard');
        } elseif ($roleLower === 'student') {
            return redirect()->route('student.dashboard');
        } elseif ($user->canAccessModule(10051)) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('dashboard');
        }
    }

    /**
     * Log the user out
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();

        // Log logout event
        if ($userId) {
            $this->auditLogger->logAuth('LOGOUT', $userId, 'User logged out');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
