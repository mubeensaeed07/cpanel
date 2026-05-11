<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('admin_id');

        if (! $adminId || ! Admin::whereKey($adminId)->exists()) {
            $request->session()->forget(['admin_id', 'admin_name', 'admin_email']);

            return redirect()->route('login')->with('error', 'Please sign in.');
        }

        return $next($request);
    }
}
