<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

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
        $tenant = Auth::user()->tenant;
        $request->validate([
            'name' => 'required|string|unique:roles,name,NULL,id,tenant_id,' . $tenant->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'tenant_id' => $tenant->id,
        ]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

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
