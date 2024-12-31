<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Buscar el tenant correspondiente
        $tenant = Tenant::findOrFail($user->tenant_id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Establecer el tenant en el contenedor de la aplicación
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
