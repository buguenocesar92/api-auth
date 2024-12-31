<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth; // o Tymon\JWTAuth\Facades\JWTAuth

class TenantRegistrationController extends Controller
{
    public function registerTenant(Request $request)
    {
        $request->validate([
            'name'      => 'required|alpha_dash|unique:tenants,domain',
            // Datos de usuario
            'user_name'  => 'required',
            'user_email' => 'required|email|unique:users,email',
            'password'   => 'required|confirmed|min:8',
        ]);

        // Generar el dominio como {subdominio}.saas.local
        $domain = "{$request->name}.saas.local";

        // 1. Crear Tenant
        $tenant = Tenant::create([
            'name'   => $request->name,
            'domain' => $domain, // Guardar el dominio generado
        ]);

        // 2. Crear usuario admin
        $user = new User();
        $user->name = $request->user_name;
        $user->email = $request->user_email;
        $user->password = Hash::make($request->password);
        $user->tenant_id = $tenant->id;
        $user->save();

        $user->assignRole('Admin'); // Asegúrate de haber creado este rol

        // 3. Loguear con JWT
        if (! $token = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'Could not generate token'], 500);
        }

        // Retornar info
        return response()->json([
            'message' => 'Tenant registered successfully',
            'tenant'  => $tenant,
            'user'    => $user,
            'access_token'   => $token,
        ], 201);
    }
}
