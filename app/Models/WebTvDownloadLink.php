<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebTvDownloadLink extends Model
{
    protected $fillable = [
        'web_tv_id',
        'label',
        'file_name',
        'file_url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'web_tv_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function webTv(): BelongsTo
    {
        return $this->belongsTo(WebTv::class);
    }
}
