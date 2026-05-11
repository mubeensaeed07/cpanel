<?php

namespace App\Models\Concerns;

trait ScopedToAdminSession
{
    public function resolveRouteBinding($value, $field = null)
    {
        $adminId = request()->session()->get('admin_id');
        abort_unless($adminId, 403);

        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('created_by', $adminId)
            ->firstOrFail();
    }
}
