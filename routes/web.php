<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGameController;
use App\Http\Controllers\AdminMovieController;
use App\Http\Controllers\AdminSeriesController;
use App\Http\Controllers\AdminSoftwareController;
use App\Http\Controllers\AdminWallpaperController;
use App\Http\Controllers\AdminWebTvController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::get('/admin/login', fn () => redirect()->route('login'));
Route::post('/admin/login', [AuthController::class, 'login']);

Route::middleware(['admin.auth', 'no.cache.auth'])->prefix('admin')->group(function (): void {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/modules/{module}', [AdminDashboardController::class, 'module'])
        ->middleware('admin.module')
        ->name('admin.modules.show');

    $adminModuleCrud = [
        ['key' => 'movies', 'uri' => 'movies', 'singular' => 'movie', 'controller' => AdminMovieController::class],
        ['key' => 'series', 'uri' => 'series', 'singular' => 'series', 'controller' => AdminSeriesController::class],
        ['key' => 'web_tv', 'uri' => 'web-tvs', 'singular' => 'web_tv', 'controller' => AdminWebTvController::class],
        ['key' => 'wallpapers', 'uri' => 'wallpapers', 'singular' => 'wallpaper', 'controller' => AdminWallpaperController::class],
        ['key' => 'games', 'uri' => 'games', 'singular' => 'game', 'controller' => AdminGameController::class],
        ['key' => 'software', 'uri' => 'software', 'singular' => 'software', 'controller' => AdminSoftwareController::class],
        ['key' => 'applications', 'uri' => 'applications', 'singular' => 'application', 'controller' => AdminApplicationController::class],
    ];

    foreach ($adminModuleCrud as $m) {
        $routePrefix = 'admin.'.$m['uri'];
        Route::middleware(['admin.module:'.$m['key']])->group(function () use ($m, $routePrefix): void {
            Route::resource($m['uri'], $m['controller'])
                ->except(['show'])
                ->parameters([$m['singular'] => 'id'])
                ->names([
                    'index' => $routePrefix.'.index',
                    'create' => $routePrefix.'.create',
                    'store' => $routePrefix.'.store',
                    'edit' => $routePrefix.'.edit',
                    'update' => $routePrefix.'.update',
                    'destroy' => $routePrefix.'.destroy',
                ]);
        });
    }
});

Route::middleware(['super_admin.auth', 'no.cache.auth'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/modules/{module}', [DashboardController::class, 'module'])->name('modules.show');

    Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::put('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
