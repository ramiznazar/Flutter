<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        // No middleware here - we'll handle it in routes
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check hardcoded credentials first (for backward compatibility)
        $hardcodedEmail = "admin@crutox.com";
        $hardcodedPassword = "admin$$$@@@";

        if ($request->username === $hardcodedEmail && $request->password === $hardcodedPassword) {
            // Create a session for hardcoded admin
            $request->session()->put('admin_id', 0);
            $request->session()->put('admin_username', 'admin');
            $request->session()->put('admin_email', $hardcodedEmail);
            $request->session()->put('admin_name', 'Admin');
            
            return redirect()->route('admin.dashboard');
        }

        // Check database admin table
        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        }

        // Update last login
        $admin->update(['last_login' => now()]);

        // Login using guard
        Auth::guard('admin')->login($admin, $request->filled('remember'));

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        // Logout from guard
        Auth::guard('admin')->logout();
        
        // Clear hardcoded admin session variables
        $request->session()->forget('admin_id');
        $request->session()->forget('admin_username');
        $request->session()->forget('admin_email');
        $request->session()->forget('admin_name');
        
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

