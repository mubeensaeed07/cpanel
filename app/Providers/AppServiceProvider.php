<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin_app', function ($view): void {
            $adminId = session('admin_id');
            $admin = $adminId ? Admin::find($adminId) : null;

            $allowedModules = $admin
                ? collect(config('modules'))->only($admin->module_permissions ?? [])->all()
                : [];

            $view->with('panelAdmin', $admin)->with('allowedModules', $allowedModules);
        });
    }
}
