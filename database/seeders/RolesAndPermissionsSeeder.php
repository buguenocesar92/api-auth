<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Tenant;
use Spatie\Multitenancy\Contracts\IsTenant;
use Illuminate\Support\Facades\Artisan;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Crear permisos globales (no atados a tenant)
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);
        Permission::firstOrCreate(['name' => 'assign roles']);

        // 3. Crear roles globales y asignar permisos
        $roleUser = Role::firstOrCreate(['name' => 'User']);
        $roleUser->givePermissionTo('view dashboard');

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo([
            'view dashboard',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
        ]);

        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super-Admin']);

        // 4. Crear Tenants y Usuarios de Ejemplo
        $this->createTenantWithUsers(
            'Acme Inc.',
            'acme.saas.local',
            'Example Admin Acme',
            'admin@acme.com',
            'admin123',
            'Example User Acme',
            'user@acme.com',
            'user123',
            $roleAdmin,
            $roleUser
        );

        $this->createTenantWithUsers(
            'Beta Inc.',
            'beta.saas.local',
            'Example Admin Beta',
            'admin@beta.com',
            'admin123',
            'Example User Beta',
            'user@beta.com',
            'user123',
            $roleAdmin,
            $roleUser
        );

        // Crear "Super-Admin" en la base de datos principal
        $this->createSuperAdmin();
    }

    /**
     * Crear un tenant y sus usuarios.
     */
    private function createTenantWithUsers(
        string $tenantName,
        string $domain,
        string $adminName,
        string $adminEmail,
        string $adminPassword,
        string $userName,
        string $userEmail,
        string $userPassword,
        Role $roleAdmin,
        Role $roleUser
    ): void {
        // Crear el tenant
        $tenant = Tenant::firstOrCreate(
            ['name' => $tenantName],
            ['domain' => $domain]
        );

        // Establecer el tenant actual
/*         $tenant->makeCurrent();

        // Verifica que el tenant se registró correctamente
        if (!app()->bound(\Spatie\Multitenancy\Contracts\IsTenant::class)) {
            $this->command->error("Failed to bind tenant {$tenant->name} to the container.");
            $tenant->forgetCurrent();
            return;
        } */


        //dd($tenant->id);

        // Ejecutar migraciones específicas del tenant
        //Artisan::call('migrate', ['--force' => true]);

        // Crear el Admin del tenant y asociarlo con el tenant_id
        $adminUser = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => bcrypt($adminPassword),
                'tenant_id' => $tenant->id, // Asociar el tenant_id
            ]
        );

        //dd($tenant->id);
        $adminUser->assignRole($roleAdmin);

        //dd($adminUser);

        // Crear el User del tenant y asociarlo con el tenant_id
        $normalUser = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => $userName,
                'password' => bcrypt($userPassword),
                'tenant_id' => $tenant->id, // Asociar el tenant_id
            ]
        );
        $normalUser->assignRole($roleUser);

        //dd($normalUser);

        // Olvidar el tenant actual
      /*   $tenant->forgetCurrent(); */

    }

    /**
     * Crear el Super Admin en la base de datos principal.
     */
    private function createSuperAdmin(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'changeme123');

        $userSuperAdmin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($password),
            ]
        );
        $userSuperAdmin->assignRole('Super-Admin');
    }
}
