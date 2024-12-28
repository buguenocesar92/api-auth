<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantRegistrationController extends Controller
{
    /**
     * Registra un nuevo usuario en el Tenant,
     * asignando "Admin" si es el primero, o "User" en caso contrario.
     */
    public function registerUserInTenant(Request $request)
    {
        // 1. Validar los campos
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        // 2. Buscar el Tenant
        $tenant = Tenant::findOrFail($request->tenant_id);

        // 3. Ver si el Tenant ya tiene usuarios
        $hasUsers = $tenant->users()->exists();

        // 4. Crear el nuevo usuario
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->tenant_id = $tenant->id;
        $user->save();

        // 5. Asignar rol
        if (! $hasUsers) {
            // Si no hay usuarios previos, el primero será Admin
            $user->assignRole('Admin');
        } else {
            // Siguientes serán rol User (o lo que desees)
            $user->assignRole('User');
        }

        // 6. Retornar la respuesta
        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'tenant'  => $tenant->name,
            'user'    => $user->only(['id', 'name', 'email']),
            'role'    => $hasUsers ? 'User' : 'Admin'
        ], 201);
    }
}
