<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_display_encrypted',
        'module_permissions',
        'connect_g_drive',
        'connect_jellyfin',
        'gdrive_last_synced_at',
        'gdrive_sync_status',
        'gdrive_last_error',
        'jellyfin_last_synced_at',
        'jellyfin_sync_status',
        'jellyfin_last_error',
    ];

    protected $hidden = [
        'password',
        'password_display_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'module_permissions' => 'array',
            'password' => 'hashed',
            'connect_g_drive' => 'boolean',
            'connect_jellyfin' => 'boolean',
            'gdrive_last_synced_at' => 'datetime',
            'jellyfin_last_synced_at' => 'datetime',
        ];
    }

    public function canAccessModule(string $module): bool
    {
        return in_array($module, $this->module_permissions ?? [], true);
    }

    /**
     * Plain password last set from this panel (encrypted at rest with APP_KEY).
     * Not available for legacy rows until the password is set again.
     */
    public function superAdminViewablePassword(): ?string
    {
        $raw = $this->attributes['password_display_encrypted'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }
        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null;
        }
    }
}
