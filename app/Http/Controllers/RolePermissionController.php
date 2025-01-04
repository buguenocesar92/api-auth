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
        return response()->json(Auth::user()->allRolesAndPermissionsForTenant());
    }
    /**
     * Crear un rol con permisos para el Tenant actual.
     */
     public function createRole(Request $request)
     {
         // Validar los datos del request
         $validator = Validator::make($request->all(), [
             'role_name' => 'required|string|max:255,name',
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
         $role = Role::firstOrCreate(['name' => $request->role_name]);

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
     /**
     * Eliminar un rol.
     */
    public function deleteRole($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        // Si el rol está asignado a usuarios, verifica si deseas bloquear la eliminación
        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return response()->json(['message' => 'No se puede eliminar un rol asignado a usuarios'], 400);
        }

        // Eliminar el rol
        $role->delete();

        return response()->json(['message' => 'Rol eliminado exitosamente'], 200);
    }

    /**
     * Actualizar un rol con sus permisos y usuarios.
     */
    public function updateRole(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        // Validar los datos del request
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:255',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Actualizar el nombre del rol
        $role->name = $request->role_name;
        $role->save();

        // Actualizar permisos
        if (!empty($request->permissions)) {
            $permissions = [];
            foreach ($request->permissions as $permissionName) {
                $permissions[] = Permission::firstOrCreate(['name' => $permissionName]);
            }
            $role->syncPermissions($permissions); // Sincronizar permisos
        } else {
            $role->permissions()->detach(); // Si no se envían permisos, elimina los existentes
        }

        // Actualizar usuarios asociados
        if (!empty($request->users)) {
            $users = User::whereIn('id', $request->users)->get();
            foreach ($users as $user) {
                $user->assignRole($role);
            }
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'), // Devuelve los permisos actualizados
            ],
        ], 200);
    }
    /**
     * Eliminar un usuario de un rol.
     */
    public function removeUserFromRole(Request $request, $roleId, $userId)
    {
        $role = Role::find($roleId);
        $user = User::find($userId);

        if (!$role || !$user) {
            return response()->json(['message' => 'Rol o usuario no encontrado'], 404);
        }

        if (!$user->hasRole($role->name)) {
            return response()->json(['message' => 'El usuario no tiene asignado este rol'], 400);
        }

        // Eliminar el rol del usuario
        $user->removeRole($role);

        // Verificar si el rol ya no tiene usuarios asignados
        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole === 0) {
            // Obtener los permisos asociados al rol antes de eliminarlo
            $permissions = $role->permissions;

            // Desvincular los permisos del rol
            $role->permissions()->detach();

            // Eliminar el rol
            $role->delete();

            // Verificar y eliminar permisos huérfanos (no asociados a ningún rol)
            foreach ($permissions as $permission) {
                $rolesWithPermission = \Spatie\Permission\Models\Role::whereHas('permissions', function ($query) use ($permission) {
                    $query->where('id', $permission->id);
                })->exists();

                if (!$rolesWithPermission) {
                    $permission->delete();
                }
            }
        }

        return response()->json(['message' => 'Usuario eliminado del rol exitosamente'], 200);
    }



    /**
     * Eliminar un permiso de un rol.
     */
    public function removePermissionFromRole(Request $request, $roleId)
    {
        // Buscar el rol por ID
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        // Validar los datos del request
        $validator = Validator::make($request->all(), [
            'permission' => 'required|string|exists:permissions,name', // Validar que el permiso existe
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Buscar el permiso por nombre
        $permission = Permission::where('name', $request->permission)->first();

        if (!$permission) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }

        // Revocar el permiso del rol
        $role->revokePermissionTo($permission);

        return response()->json(['message' => 'Permiso eliminado del rol exitosamente'], 200);
    }

}
