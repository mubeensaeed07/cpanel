<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminGoogleDriveController extends Controller
{
    public function __construct(private readonly GoogleDriveService $service) {}

    public function index(): View
    {
        return view('integrations.google_drive', [
            'connection' => $this->service->getConnection(),
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        $state = bin2hex(random_bytes(16));
        $request->session()->put('google_drive_oauth_state', $state);

        return redirect()->away($this->service->authorizationUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('google_drive_oauth_state', '');
        $state = (string) $request->query('state', '');
        if ($expectedState === '' || $state === '' || ! hash_equals($expectedState, $state)) {
            return redirect()->route('integrations.google-drive.index')
                ->withErrors(['google_drive' => 'Google OAuth state mismatch. Try connect again.']);
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('integrations.google-drive.index')
                ->withErrors(['google_drive' => 'Google did not return an authorization code.']);
        }

        try {
            $this->service->exchangeCode($code);
        } catch (\Throwable $e) {
            return redirect()->route('integrations.google-drive.index')
                ->withErrors(['google_drive' => $e->getMessage()]);
        }

        return redirect()->route('integrations.google-drive.index')
            ->with('status', 'Google Drive connected successfully.');
    }

    public function disconnect(): RedirectResponse
    {
        $this->service->disconnect();

        return redirect()->route('integrations.google-drive.index')->with('status', 'Google Drive disconnected.');
    }
}
