<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\SeriesDownloadLink;

class AdminSeriesController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.series',
            'model' => Series::class,
            'link_model' => SeriesDownloadLink::class,
            'foreign_key' => 'series_id',
            'slug_fallback' => 'series',
            'label' => 'Series',
            'entity_singular' => 'series',
            'route_param' => 'id',
        ];
    }
}
