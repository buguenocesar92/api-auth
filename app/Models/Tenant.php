<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements IsTenant
{
    protected $fillable = ['name', 'domain'];

    // Opcional: Relación con usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
