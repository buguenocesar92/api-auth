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
            'domain' => 'required|unique:tenants,domain',
            'name' => 'required|unique:tenants,name',
            // datos de usuario
            'user_name' => 'required',
            'user_email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        // 1. Crear Tenant
        $tenant = Tenant::create([
            'name'   => $request->name,
            'domain' => $request->domain,
            // otros campos según config
        ]);

        // 2. Crear usuario admin
        $user = new User();
        $user->name = $request->user_name;
        $user->email = $request->user_email;
        $user->password = Hash::make($request->password);
        // Podrías guardar tenant_id si usas single DB.
        // O si usas "database per tenant", en la migración de la base "landlord"
        // no es necesario. Depende de tu approach.
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
            'token'   => $token,
        ], 201);
    }
}
