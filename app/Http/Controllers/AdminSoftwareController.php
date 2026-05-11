<?php

namespace App\Http\Controllers;

use App\Models\Software;
use App\Models\SoftwareDownloadLink;

class AdminSoftwareController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.software',
            'model' => Software::class,
            'link_model' => SoftwareDownloadLink::class,
            'foreign_key' => 'software_id',
            'slug_fallback' => 'software',
            'label' => 'Software',
            'entity_singular' => 'software',
            'route_param' => 'id',
        ];
    }
}
