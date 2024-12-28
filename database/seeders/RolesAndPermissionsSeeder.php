<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear permisos
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'assign roles']);
        // ...agrega otros si lo deseas

        // 3. Crear roles y asignar permisos

        // -- Rol "User" (rol básico, con permiso para ver el dashboard)
        $roleUser = Role::create(['name' => 'User']);
        $roleUser->givePermissionTo('view dashboard');

        // -- Rol "Admin"
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleAdmin->givePermissionTo([
            'view dashboard',
            'create users',
            'edit users',
            'delete users',
            'assign roles'
        ]);

        // -- Rol "Super-Admin" (puede tener lógica de Gate::before en AuthServiceProvider)
        $roleSuperAdmin = Role::create(['name' => 'Super-Admin']);
        // No es necesario asignar permisos específicos
        // si tu AuthServiceProvider maneja el "Super-Admin" => todos los permisos.

        // 4. Crear usuarios de ejemplo con roles
        // -- Usuario con rol "User"
        $userUser = User::factory()->create([
            'name' => 'Example User',
            'email' => 'user@example.com',
        ]);
        $userUser->assignRole($roleUser);

        // -- Usuario con rol "Admin"
        $userAdmin = User::factory()->create([
            'name' => 'Example Admin',
            'email' => 'admin@example.com',
        ]);
        $userAdmin->assignRole($roleAdmin);

        // -- Usuario con rol "Super-Admin"
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        $userSuperAdmin = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($password),
            ]
        );
        $userSuperAdmin->assignRole('Super-Admin');

    }
}
