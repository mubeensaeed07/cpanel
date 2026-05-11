<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse|Response
    {
        if ($request->session()->get('super_admin_authenticated', false)) {
            return redirect()->route('dashboard');
        }

        if ($request->session()->get('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validated['email'] === config('super_admin.email')
            && $validated['password'] === config('super_admin.password')) {
            $request->session()->forget(['admin_id', 'admin_name', 'admin_email']);
            $request->session()->regenerate();
            $request->session()->put('super_admin_authenticated', true);
            $request->session()->put('super_admin_email', $validated['email']);

            return redirect()->route('dashboard');
        }

        $admin = Admin::where('email', $validated['email'])->first();
        if ($admin && Hash::check($validated['password'], $admin->password)) {
            $request->session()->forget(['super_admin_authenticated', 'super_admin_email']);
            $request->session()->regenerate();
            $request->session()->put('admin_id', $admin->id);
            $request->session()->put('admin_name', $admin->name);
            $request->session()->put('admin_email', $admin->email);

            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withInput($request->except('password'))
            ->withErrors(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['super_admin_authenticated', 'super_admin_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Logged out successfully.')
            ->withHeaders([
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
    }
}
