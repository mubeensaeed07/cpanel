<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $modules = config('modules');

        return view('dashboard.index', [
            'modules' => $modules,
            'stats' => [
                'admins' => Admin::count(),
                'modules' => count($modules),
            ],
        ]);
    }

    public function module(string $module): View
    {
        $modules = config('modules');
        abort_unless(array_key_exists($module, $modules), 404);

        return view('dashboard.module', [
            'moduleKey' => $module,
            'moduleName' => $modules[$module],
        ]);
    }
}
