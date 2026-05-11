<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDownloadLink;

class AdminApplicationController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.applications',
            'model' => Application::class,
            'link_model' => ApplicationDownloadLink::class,
            'foreign_key' => 'application_id',
            'slug_fallback' => 'application',
            'label' => 'Applications',
            'entity_singular' => 'application',
            'route_param' => 'application',
        ];
    }
}
