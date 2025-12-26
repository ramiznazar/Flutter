<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        // Handle hardcoded admin session
        if (!$admin && session('admin_id') === 0) {
            $admin = (object) [
                'id' => 0,
                'username' => session('admin_username', 'admin'),
                'email' => session('admin_email', 'admin@crutox.com'),
                'name' => session('admin_name', 'Admin'),
                'created_at' => now(),
                'last_login' => null,
            ];
        }

        return view('admin.profile', compact('admin'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6',
            'confirm_password' => 'nullable|string|same:new_password',
        ]);

        $admin = Auth::guard('admin')->user();
        $isHardcodedAdmin = false;

        // Handle hardcoded admin session
        if (!$admin && session('admin_id') === 0) {
            $isHardcodedAdmin = true;
            // For hardcoded admin, just update session
            $request->session()->put('admin_username', $request->username);
            $request->session()->put('admin_name', $request->name);
            $request->session()->put('admin_email', $request->email);
            
            // Handle password change for hardcoded admin
            if ($request->filled('new_password')) {
                if ($request->current_password !== 'admin$$$@@@') {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
                }
                // Note: Hardcoded admin password can't be changed in database
                // You might want to create a real admin account for this
            }

            return redirect()->route('admin.profile')
                ->with('message', 'Profile updated successfully.')
                ->with('messageType', 'success');
        }

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Check if username is already taken by another admin
        $usernameExists = Admin::where('username', $request->username)
            ->where('id', '!=', $admin->id)
            ->exists();

        if ($usernameExists) {
            return back()->withErrors(['username' => 'Username is already taken by another admin.'])->withInput();
        }

        // Check if email is already taken by another admin
        $emailExists = Admin::where('email', $request->email)
            ->where('id', '!=', $admin->id)
            ->exists();

        if ($emailExists) {
            return back()->withErrors(['email' => 'Email is already taken by another admin.'])->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password')) {
                return back()->withErrors(['current_password' => 'Please enter current password to change password.'])->withInput();
            }

            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $updateData['password'] = Hash::make($request->new_password);
        }

        $admin->update($updateData);

        return redirect()->route('admin.profile')
            ->with('message', 'Profile updated successfully.')
            ->with('messageType', 'success');
    }
}

