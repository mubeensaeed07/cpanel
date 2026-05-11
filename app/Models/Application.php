<?php

namespace App\Models;

use App\Models\Concerns\ScopedToAdminSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use ScopedToAdminSession;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function downloadLinks(): HasMany
    {
        return $this->hasMany(ApplicationDownloadLink::class)->orderBy('sort_order');
    }
}
