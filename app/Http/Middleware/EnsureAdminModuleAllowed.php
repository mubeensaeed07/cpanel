<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminModuleAllowed
{
    public function handle(Request $request, Closure $next, ?string $module = null): Response
    {
        $moduleKey = $module ?? $request->route('module');
        $adminId = $request->session()->get('admin_id');

        if (! is_string($moduleKey) || $moduleKey === '' || ! $adminId) {
            abort(403);
        }

        $admin = Admin::find($adminId);
        if (! $admin) {
            abort(403);
        }

        $allowed = $admin->module_permissions ?? [];
        if (! in_array($moduleKey, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
