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
     * Roles relacionados con el tenant del usuario.
     */
    public function rolesForTenant()
    {
        return $this->roles()->whereHas('users', function ($query) {
            $query->where('tenant_id', $this->tenant_id);
        });
    }

    /**
     * Permisos relacionados con el tenant del usuario.
     */
    public function permissionsForTenant()
    {
        return $this->permissions()->whereHas('roles', function ($query) {
            $query->whereHas('users', function ($subQuery) {
                $subQuery->where('tenant_id', $this->tenant_id);
            });
        });
    }


/**
 * Obtener roles con permisos relacionados para el tenant del usuario.
 */
public function allRolesAndPermissionsForTenant()
{
    return \Spatie\Permission\Models\Role::select('roles.*')
        ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->join('users', 'model_has_roles.model_id', '=', 'users.id')
        ->where('model_has_roles.model_type', User::class) // Asegúrate de usar el modelo correcto
        ->where('users.tenant_id', $this->tenant_id) // Filtrar por tenant_id
        ->distinct() // Evitar duplicados
        ->with(['permissions']) // Cargar permisos relacionados
        ->get()
        ->map(function ($role) {
            // Filtrar usuarios directamente aquí
            $users = $role->users->filter(function ($user) {
                return $user->tenant_id === $this->tenant_id;
            })->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'users' => $users,
            ];
        });
}


}
