<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admins.index', [
            'admins' => Admin::latest()->paginate(10),
            'modules' => config('modules'),
        ]);
    }

    public function create(): View
    {
        return view('admins.create', [
            'modules' => config('modules'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $moduleKeys = array_keys(config('modules'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'module_permissions' => ['required', 'array', 'min:1'],
            'module_permissions.*' => ['required', 'string', 'in:'.implode(',', $moduleKeys)],
        ]);

        $plain = $validated['password'];
        Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $plain,
            'password_display_encrypted' => Crypt::encryptString($plain),
            'module_permissions' => array_values(array_unique($validated['module_permissions'])),
        ]);

        return redirect()->route('admins.index')->with('status', 'Admin created successfully.');
    }

    public function show(Admin $admin): View
    {
        return view('admins.show', [
            'admin' => $admin,
            'modules' => config('modules'),
        ]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $moduleKeys = array_keys(config('modules'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,'.$admin->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'reveal_current_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'module_permissions' => ['required', 'array', 'min:1'],
            'module_permissions.*' => ['required', 'string', 'in:'.implode(',', $moduleKeys)],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'module_permissions' => array_values(array_unique($validated['module_permissions'])),
        ];

        if (! empty($validated['password'])) {
            $plain = $validated['password'];
            $payload['password'] = $plain;
            $payload['password_display_encrypted'] = Crypt::encryptString($plain);
        } elseif (! empty($validated['reveal_current_password'])) {
            $typed = $validated['reveal_current_password'];
            $hash = $admin->getAttributes()['password'] ?? '';
            if (! is_string($hash) || ! Hash::check($typed, $hash)) {
                return redirect()->route('admins.show', $admin)
                    ->withErrors(['reveal_current_password' => 'That does not match this admin’s current login password.'])
                    ->withInput($request->except([
                        'password',
                        'password_confirmation',
                        'reveal_current_password',
                        'reveal_current_password_confirmation',
                    ]));
            }
            $payload['password_display_encrypted'] = Crypt::encryptString($typed);
        }

        $admin->update($payload);

        return redirect()->route('admins.show', $admin)->with('status', 'Admin updated successfully.');
    }
}
