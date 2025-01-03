<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function routes()
    {
        return $this->hasMany(Route::class); // Relación con las rutas
    }
}
