<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RolePermissionController extends Controller
{
    /**
     * Listar roles y permisos por Tenant.
     */
    public function listRolesWithPermissions()
    {
        // Retornar la respuesta en formato JSON
        return response()->json(Auth::user()->allRolesAndPermissionsForTenant());
    }


    /**
     * Crear un rol con permisos para el Tenant actual.
     */
     public function createRole(Request $request)
     {
         // Validar los datos del request
         $validator = Validator::make($request->all(), [
             'role_name' => 'required|string|max:255|unique:roles,name',
             'permissions' => 'required|array',
             'permissions.*' => 'string|max:255',
             'users' => 'nullable|array',
             'users.*' => 'exists:users,id',
         ]);

         if ($validator->fails()) {
             // Retornar errores de validación si fallan
             return response()->json(['errors' => $validator->errors()], 400);
         }

         // Crear el rol
         $role = Role::create(['name' => $request->role_name]);

         // Crear los permisos si no existen y asignarlos al rol
         foreach ($request->permissions as $permissionName) {
             $permission = Permission::firstOrCreate(['name' => $permissionName]);
             $role->givePermissionTo($permission);
         }

         // Asignar el rol a los usuarios seleccionados
         if (!empty($request->users)) {
             $users = User::whereIn('id', $request->users)->get();
             foreach ($users as $user) {
                 $user->assignRole($role);
             }
         }

         // Respuesta exitosa
         return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions // Devuelve los permisos asignados al rol
            ],
        ], 201);

     }

/*
     public function createRole(Request $request)
     {
        $user = User::find(1); // Cambia el ID según corresponda

        // Crear un rol
        $role = Role::create(['name' => 'Test Role']);

        // Crear permisos
        $permissions = ['test_permission_1', 'test_permission_2'];
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Asignar permisos al rol
        $role->syncPermissions($permissions);

        // Asignar el rol al usuario
        $user->assignRole($role);

     } */

    /**
     * Crear un permiso para el Tenant actual.
     */
/*     public function createPermission(Request $request)
    {
        $tenant = app()->make('tenant');

        if ($tenant instanceof \Illuminate\Http\JsonResponse) {
            return $tenant;
        }

        $request->validate([
            'name' => 'required|string|unique:permissions,name,NULL,id,tenant_id,' . $tenant->id,
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'tenant_id' => $tenant->id,
        ]);

        return response()->json(['message' => 'Permission created successfully', 'permission' => $permission], 201);
    } */

    /**
     * Asignar rol a un usuario dentro del Tenant actual.
     */
/*     public function assignRoleToUser(Request $request)
    {
        $tenant = $this->getCurrentTenant();

        if ($tenant instanceof \Illuminate\Http\JsonResponse) {
            return $tenant;
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::where('tenant_id', $tenant->id)->findOrFail($request->user_id);
        $role = Role::where('tenant_id', $tenant->id)->where('name', $request->role)->firstOrFail();

        $user->assignRole($role);

        return response()->json(['message' => "Role '{$role->name}' assigned to user '{$user->name}'"], 200);
    } */

    /**
     * Asignar permiso a un rol dentro del Tenant actual.
     */
/*     public function assignPermissionToRole(Request $request)
    $tenant = app()->make('tenant');
        if ($tenant instanceof \Illuminate\Http\JsonResponse) {
            return $tenant;
        }

        $request->validate([
            'role' => 'required|exists:roles,name',
            'permission' => 'required|exists:permissions,name',
        ]);

        $role = Role::where('tenant_id', $tenant->id)->where('name', $request->role)->firstOrFail();
        $permission = Permission::where('tenant_id', $tenant->id)->where('name', $request->permission)->firstOrFail();

        $role->givePermissionTo($permission);

        return response()->json(['message' => "Permission '{$permission->name}' assigned to role '{$role->name}'"], 200);
    } */
}
