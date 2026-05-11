<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameDownloadLink;

class AdminGameController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.games',
            'model' => Game::class,
            'link_model' => GameDownloadLink::class,
            'foreign_key' => 'game_id',
            'slug_fallback' => 'game',
            'label' => 'Games',
            'entity_singular' => 'game',
            'route_param' => 'game',
        ];
    }
}
