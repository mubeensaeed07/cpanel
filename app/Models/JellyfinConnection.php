<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JellyfinConnection extends Model
{
    protected $fillable = [
        'server_url',
        'api_key',
        'user_id',
        'server_name',
    ];
}
