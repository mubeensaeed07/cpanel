<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameDownloadLink extends Model
{
    protected $fillable = [
        'game_id',
        'label',
        'file_name',
        'file_url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'game_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
