<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleDriveConnection extends Model
{
    protected $fillable = [
        'provider',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'google_email',
        'google_name',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
        ];
    }
}
