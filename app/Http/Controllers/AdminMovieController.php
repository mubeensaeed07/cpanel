<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieDownloadLink;

class AdminMovieController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.movies',
            'model' => Movie::class,
            'link_model' => MovieDownloadLink::class,
            'foreign_key' => 'movie_id',
            'slug_fallback' => 'movie',
            'label' => 'Movies',
            'entity_singular' => 'movie',
            'route_param' => 'movie',
        ];
    }
}
