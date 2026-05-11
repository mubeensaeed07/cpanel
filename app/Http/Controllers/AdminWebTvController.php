<?php

namespace App\Http\Controllers;

use App\Models\WebTv;
use App\Models\WebTvDownloadLink;

class AdminWebTvController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.web-tvs',
            'model' => WebTv::class,
            'link_model' => WebTvDownloadLink::class,
            'foreign_key' => 'web_tv_id',
            'slug_fallback' => 'web-tv',
            'label' => 'Web TV',
            'entity_singular' => 'channel',
            'route_param' => 'web_tv',
        ];
    }
}
