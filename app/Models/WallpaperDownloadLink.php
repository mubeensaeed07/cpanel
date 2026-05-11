<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WallpaperDownloadLink extends Model
{
    protected $fillable = [
        'wallpaper_id',
        'label',
        'file_name',
        'file_url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'wallpaper_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function wallpaper(): BelongsTo
    {
        return $this->belongsTo(Wallpaper::class);
    }
}
