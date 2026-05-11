<?php

namespace App\Http\Controllers;

use App\Models\Wallpaper;
use App\Models\WallpaperDownloadLink;

class AdminWallpaperController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.wallpapers',
            'model' => Wallpaper::class,
            'link_model' => WallpaperDownloadLink::class,
            'foreign_key' => 'wallpaper_id',
            'slug_fallback' => 'wallpaper',
            'label' => 'Wallpapers',
            'entity_singular' => 'wallpaper',
            'route_param' => 'wallpaper',
        ];
    }
}
