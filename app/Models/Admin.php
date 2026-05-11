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
