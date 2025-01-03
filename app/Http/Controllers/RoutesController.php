<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutesController extends Controller
{
    public function getAccessibleRoutes(Request $request)
    {
        $user = $request->user(); // Obtener usuario autenticado

        // Filtrar rutas según los permisos del usuario
        $routes = DB::table('routes')
            ->when($user, function ($query) use ($user) {
                $permissions = $user->getAllPermissions()->pluck('name'); // Obtener permisos del usuario
                $query->whereIn('required_permission', $permissions)
                      ->orWhereNull('required_permission'); // Incluir rutas públicas
            })
            ->get();

        return response()->json(['routes' => $routes]);
    }
}
