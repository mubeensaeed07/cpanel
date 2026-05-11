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
use App\Http\Controllers\SuperAdminGoogleDriveController;
use App\Http\Controllers\SuperAdminJellyfinController;
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

    Route::middleware(['admin.module:movies'])
        ->get('/movies/imdb/lookup', [AdminMovieController::class, 'imdbLookup'])
        ->name('admin.movies.imdb.lookup');
});

Route::middleware(['super_admin.auth', 'no.cache.auth'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/modules/{module}', [DashboardController::class, 'module'])->name('modules.show');

    Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
    Route::put('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
    Route::post('/admins/{admin}/gdrive/fetch', [AdminController::class, 'fetchGoogleDrive'])->name('admins.gdrive.fetch');
    Route::post('/admins/{admin}/jellyfin/fetch', [AdminController::class, 'fetchJellyfin'])->name('admins.jellyfin.fetch');

    Route::get('/integrations/google-drive', [SuperAdminGoogleDriveController::class, 'index'])->name('integrations.google-drive.index');
    Route::get('/integrations/google-drive/redirect', [SuperAdminGoogleDriveController::class, 'redirect'])->name('integrations.google-drive.redirect');
    Route::get('/integrations/google-drive/callback', [SuperAdminGoogleDriveController::class, 'callback'])->name('integrations.google-drive.callback');
    Route::delete('/integrations/google-drive', [SuperAdminGoogleDriveController::class, 'disconnect'])->name('integrations.google-drive.disconnect');
    Route::get('/integrations/jellyfin', [SuperAdminJellyfinController::class, 'index'])->name('integrations.jellyfin.index');
    Route::post('/integrations/jellyfin', [SuperAdminJellyfinController::class, 'save'])->name('integrations.jellyfin.save');
    Route::delete('/integrations/jellyfin', [SuperAdminJellyfinController::class, 'disconnect'])->name('integrations.jellyfin.disconnect');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
