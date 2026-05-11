<?php

namespace App\Http\Controllers;

use App\Services\JellyfinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminJellyfinController extends Controller
{
    public function __construct(private readonly JellyfinService $jellyfin) {}

    public function index(): View
    {
        return view('integrations.jellyfin', [
            'connection' => $this->jellyfin->getConnection(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_url' => ['required', 'url', 'max:255'],
            'api_key' => ['required', 'string', 'max:500'],
            'user_id' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->jellyfin->saveConnection($validated['server_url'], $validated['api_key'], $validated['user_id']);
        } catch (\Throwable $e) {
            return redirect()->route('integrations.jellyfin.index')
                ->withErrors(['jellyfin' => $e->getMessage()])
                ->withInput($request->except('api_key'));
        }

        return redirect()->route('integrations.jellyfin.index')->with('status', 'Jellyfin connected successfully.');
    }

    public function disconnect(): RedirectResponse
    {
        $this->jellyfin->disconnect();

        return redirect()->route('integrations.jellyfin.index')->with('status', 'Jellyfin disconnected.');
    }
}
