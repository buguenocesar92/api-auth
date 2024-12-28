<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Tenant;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions with a basic multi-tenant approach.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear permisos (globales, no atados a tenant)
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'assign roles']);
        // ...agrega otros si lo deseas

        // 3. Crear roles (globales) y asignar permisos
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
            'assign roles',
        ]);

        // -- Rol "Super-Admin" (Gate::before en AuthServiceProvider)
        $roleSuperAdmin = Role::create(['name' => 'Super-Admin']);

        /*
         |--------------------------------------------------------------------------
         | 4. Crear Tenant "Acme Inc." y Usuarios
         |--------------------------------------------------------------------------
         */
        $tenantA = Tenant::firstOrCreate(
            ['name' => 'Acme Inc.'],
            [
                'domain' => 'acme.saas.local', // Opcional
                'plan' => 'pro',              // Opcional
            ]
        );

        // -- Usuario "User" en Acme
        $userUser = User::factory()->create([
            'name' => 'Example User',
            'email' => 'user@example.com',
            'tenant_id' => $tenantA->id,
        ]);
        $userUser->assignRole($roleUser);

        // -- Usuario "Admin" en Acme
        $userAdmin = User::factory()->create([
            'name' => 'Example Admin',
            'email' => 'admin@example.com',
            'tenant_id' => $tenantA->id,
        ]);
        $userAdmin->assignRole($roleAdmin);

        // -- "Super-Admin" (tomado de .env)
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'changeme123');

        $userSuperAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($password),
                // Opcional: Podrías asignarlo a algún tenant si quieres.
            ]
        );
        $userSuperAdmin->assignRole($roleSuperAdmin);

        /*
         |--------------------------------------------------------------------------
         | 5. Crear Otro Tenant "Beta Inc." y Usuarios
         |--------------------------------------------------------------------------
         */
        $tenantB = Tenant::firstOrCreate(
            ['name' => 'Beta Inc.'],
            [
                'domain' => 'beta.saas.local',
                'plan' => 'free',
            ]
        );

        // -- Usuario "User" en Beta
        $betaUser = User::factory()->create([
            'name' => 'Beta User',
            'email' => 'beta_user@example.com',
            'tenant_id' => $tenantB->id,
        ]);
        $betaUser->assignRole($roleUser);

        // -- Usuario "Admin" en Beta
        $betaAdmin = User::factory()->create([
            'name' => 'Beta Admin',
            'email' => 'beta_admin@example.com',
            'tenant_id' => $tenantB->id,
        ]);
        $betaAdmin->assignRole($roleAdmin);

        // Opcional: podrías crear más usuarios en Beta si lo deseas.
    }
}
