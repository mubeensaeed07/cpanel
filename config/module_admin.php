<?php

use App\Models\Application;
use App\Models\Game;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Software;
use App\Models\Wallpaper;
use App\Models\WebTv;

/**
 * Maps module keys (see config/modules.php) to admin route name prefixes for CRUD (e.g. admin.movies).
 *
 * @var array{routes: array<string, string>, content_models: array<string, class-string<\Illuminate\Database\Eloquent\Model>>}
 */
return [
    'routes' => [
        'movies' => 'admin.movies',
        'series' => 'admin.series',
        'web_tv' => 'admin.web-tvs',
        'wallpapers' => 'admin.wallpapers',
        'games' => 'admin.games',
        'software' => 'admin.software',
        'applications' => 'admin.applications',
    ],
    'content_models' => [
        'movies' => Movie::class,
        'series' => Series::class,
        'web_tv' => WebTv::class,
        'wallpapers' => Wallpaper::class,
        'games' => Game::class,
        'software' => Software::class,
        'applications' => Application::class,
    ],
];
