<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDownloadLink extends Model
{
    protected $fillable = [
        'application_id',
        'label',
        'file_name',
        'file_url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'application_id' => 'integer',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
