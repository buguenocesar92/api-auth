<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'tenant_id'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Obtener roles con permisos relacionados para el tenant del usuario.
     */
    public function allRolesAndPermissionsForTenant()
    {
        // Obtener todos los roles excluyendo el rol Admin
        $roles = \Spatie\Permission\Models\Role::where('name', '!=', 'Admin')
            ->with(['permissions'])
            ->get();

        // Mapear los roles para incluir permisos y usuarios relacionados con el tenant
        return $roles->map(function ($role) {
            // Obtener usuarios que pertenecen al rol y al tenant actual
            $users = User::role($role->name)
                ->where('tenant_id', $this->tenant_id)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                });

            // Si el rol no tiene usuarios asociados al tenant, devolver permisos vacíos
            $permissions = $users->isEmpty() ? collect() : $role->permissions;

            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $permissions->pluck('name'), // Lista de permisos
                'users' => $users, // Lista de usuarios
            ];
        })->filter(function ($role) {
            // Si deseas excluir roles sin usuarios ni permisos, aplica este filtro
            return $role['users']->isNotEmpty() || $role['permissions']->isNotEmpty();
        });
    }



}
