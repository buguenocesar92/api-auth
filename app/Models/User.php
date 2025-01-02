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
        // Obtener todos los roles asociados a usuarios del tenant actual
        $roles = \Spatie\Permission\Models\Role::whereHas('users', function ($query) {
            $query->where('tenant_id', $this->tenant_id);
        })
        ->with(['permissions']) // Cargar permisos relacionados
        ->get();

        // Mapear los roles para incluir permisos y usuarios
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

            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'), // Lista de permisos
                'users' => $users, // Lista de usuarios
            ];
        });
    }

}
