<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = ['path', 'component_name', 'requires_auth', 'permission_id'];

    public function permission()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Permission::class);
    }
}
