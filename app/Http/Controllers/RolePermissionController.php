<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Listar todos los roles.
     */
    public function listRoles()
    {
        return response()->json(Role::all(), 200);
    }

    /**
     * Listar todos los permisos.
     */
    public function listPermissions()
    {
        return response()->json(Permission::all(), 200);
    }

    /**
     * Crear un rol con permisos.
     */
    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    /**
     * Crear un permiso.
     */
    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        return response()->json(['message' => 'Permission created successfully', 'permission' => $permission], 201);
    }

    /**
     * Asignar rol a un usuario.
     */
    public function assignRoleToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json(['message' => "Role '{$request->role}' assigned to user '{$user->name}'"], 200);
    }

    /**
     * Asignar permiso a un rol.
     */
    public function assignPermissionToRole(Request $request)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
            'permission' => 'required|exists:permissions,name',
        ]);

        $role = Role::findByName($request->role);
        $role->givePermissionTo($request->permission);

        return response()->json(['message' => "Permission '{$request->permission}' assigned to role '{$request->role}'"], 200);
    }
}
