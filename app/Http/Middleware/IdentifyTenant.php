<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Buscar el tenant correspondiente
        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Establecer el tenant en el contenedor de la aplicación
        app()->instance('tenant', $tenant);

        // Opcional: Agregar tenant_id a la request
        $request->merge(['tenant_id' => $tenant->id]);

        return $next($request);
    }
}

