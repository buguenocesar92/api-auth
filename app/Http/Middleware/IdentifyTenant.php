<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use App\Models\Tenant;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica si el tenant ya está configurado
       // if (app()->bound(IsTenant::class)) {
         //   return $next($request);
        //}

        // Busca el tenant por dominio (o cualquier otra lógica)
        $tenant = Tenant::where('domain', $request->getHost())->first();

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        try {
            // Establece el tenant actual
            $tenant->makeCurrent();
            dd($tenant);
        } catch (\Exception $e) {
            abort(500, 'Failed to set tenant: ' . $e->getMessage());
        }

        // Procesa la solicitud
        $response = $next($request);

        // Libera el tenant después de la respuesta
        $tenant->forgetCurrent();

        return $response;
    }
}
