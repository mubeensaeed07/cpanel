<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieDownloadLink extends Model
{
    protected $fillable = [
        'movie_id',
        'label',
        'file_name',
        'file_url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'movie_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
