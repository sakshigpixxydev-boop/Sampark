<?php

use Illuminate\Support\Facades\Route;
use App\Models\SysUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Temporary debug route - remove after fixing login
Route::get('/debug-login', function () {
    $username = 'admin';
    $password = 'password';
    
    $user = SysUser::where('username', $username)->first();
    
    $debug = [
        'user_found' => $user ? true : false,
        'username' => $user->username ?? null,
        'email' => $user->email ?? null,
        'inactive' => $user->inactive ?? null,
        'role_id' => $user->role_id ?? null,
        'password_hash_preview' => $user ? substr($user->pswd, 0, 20) . '...' : null,
        'password_check' => $user ? Hash::check($password, $user->pswd) : false,
        'auth_password_method' => $user ? method_exists($user, 'getAuthPassword') : false,
        'role_loaded' => false,
        'role_name' => null,
    ];
    
    if ($user) {
        try {
            $role = $user->role;
            $debug['role_loaded'] = $role ? true : false;
            $debug['role_name'] = $role->rolename ?? null;
        } catch (\Exception $e) {
            $debug['role_error'] = $e->getMessage();
        }
        
        // Try manual login
        try {
            Auth::login($user);
            $debug['manual_login'] = 'success';
            $debug['auth_check'] = Auth::check();
            $debug['auth_id'] = Auth::id();
            $debug['auth_user'] = Auth::user()->username ?? null;
        } catch (\Exception $e) {
            $debug['manual_login'] = 'failed: ' . $e->getMessage();
        }
    }
    
    return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
});
