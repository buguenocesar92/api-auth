<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant;

class TenantModel extends Model
{
    // Aplica automáticamente el scope para filtrar por tenant_id
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenant = app(Tenant::class)) {
                $builder->where('tenant_id', $tenant->id);
            }
        });
    }

    // Asigna el tenant_id automáticamente al crear registros
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($tenant = app(Tenant::class)) {
                $model->tenant_id = $tenant->id;
            }
        });
    }
}
