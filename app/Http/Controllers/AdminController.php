<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAdminFromJellyfin;
use App\Jobs\SyncAdminGoogleDriveContent;
use App\Models\Admin;
use App\Services\GoogleDriveService;
use App\Services\JellyfinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly GoogleDriveService $googleDriveService,
        private readonly JellyfinService $jellyfinService
    ) {}

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
            'driveConnected' => $this->googleDriveService->getConnection() !== null,
            'jellyfinConnected' => $this->jellyfinService->getConnection() !== null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $moduleKeys = array_keys(config('modules'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'connect_g_drive' => ['nullable', 'boolean'],
            'connect_jellyfin' => ['nullable', 'boolean'],
            'module_permissions' => ['required', 'array', 'min:1'],
            'module_permissions.*' => ['required', 'string', 'in:'.implode(',', $moduleKeys)],
        ]);
        $wantsDrive = (bool) ($validated['connect_g_drive'] ?? false);
        $wantsJellyfin = (bool) ($validated['connect_jellyfin'] ?? false);
        if ($wantsDrive && ! $this->googleDriveService->getConnection()) {
            return redirect()->route('admins.create')
                ->withErrors(['connect_g_drive' => 'Connect Google Drive in Super Admin section first.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
        if ($wantsJellyfin && ! $this->jellyfinService->getConnection()) {
            return redirect()->route('admins.create')
                ->withErrors(['connect_jellyfin' => 'Connect Jellyfin in Super Admin section first.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $plain = $validated['password'];
        Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $plain,
            'password_display_encrypted' => Crypt::encryptString($plain),
            'connect_g_drive' => $wantsDrive,
            'connect_jellyfin' => $wantsJellyfin,
            'module_permissions' => array_values(array_unique($validated['module_permissions'])),
            'gdrive_sync_status' => null,
            'gdrive_last_error' => null,
            'gdrive_last_synced_at' => null,
            'jellyfin_sync_status' => null,
            'jellyfin_last_error' => null,
            'jellyfin_last_synced_at' => null,
        ]);

        return redirect()->route('admins.index')->with('status', 'Admin created successfully.');
    }

    public function show(Admin $admin): View
    {
        return view('admins.show', [
            'admin' => $admin,
            'modules' => config('modules'),
            'driveConnected' => $this->googleDriveService->getConnection() !== null,
            'jellyfinConnected' => $this->jellyfinService->getConnection() !== null,
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
            'connect_g_drive' => ['nullable', 'boolean'],
            'connect_jellyfin' => ['nullable', 'boolean'],
            'module_permissions' => ['required', 'array', 'min:1'],
            'module_permissions.*' => ['required', 'string', 'in:'.implode(',', $moduleKeys)],
        ]);
        $wantsDrive = (bool) ($validated['connect_g_drive'] ?? false);
        $wantsJellyfin = (bool) ($validated['connect_jellyfin'] ?? false);
        if ($wantsDrive && ! $this->googleDriveService->getConnection()) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['connect_g_drive' => 'Connect Google Drive in Super Admin section first.'])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'reveal_current_password',
                    'reveal_current_password_confirmation',
                ]));
        }
        if ($wantsJellyfin && ! $this->jellyfinService->getConnection()) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['connect_jellyfin' => 'Connect Jellyfin in Super Admin section first.'])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'reveal_current_password',
                    'reveal_current_password_confirmation',
                ]));
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'module_permissions' => array_values(array_unique($validated['module_permissions'])),
            'connect_g_drive' => $wantsDrive,
            'connect_jellyfin' => $wantsJellyfin,
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

    public function fetchGoogleDrive(Admin $admin): RedirectResponse
    {
        if (! $this->googleDriveService->getConnection()) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['gdrive' => 'Connect Google Drive from Integrations first.']);
        }

        if (! $admin->connect_g_drive) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['gdrive' => 'Enable "Connect G Drive" for this admin first, then fetch.']);
        }

        SyncAdminGoogleDriveContent::dispatch((int) $admin->getKey());

        return redirect()->route('admins.show', $admin)
            ->with('status', 'Google Drive fetch started. It may take time for large libraries.');
    }

    public function fetchJellyfin(Admin $admin): RedirectResponse
    {
        if (! $this->jellyfinService->getConnection()) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['jellyfin' => 'Connect Jellyfin from Integrations first.']);
        }

        if (! $admin->connect_jellyfin) {
            return redirect()->route('admins.show', $admin)
                ->withErrors(['jellyfin' => 'Enable "Connect Jellyfin" for this admin first, then scan.']);
        }

        SyncAdminFromJellyfin::dispatch((int) $admin->getKey());

        return redirect()->route('admins.show', $admin)
            ->with('status', 'Jellyfin scan+sync started. Large libraries may take time.');
    }
}
