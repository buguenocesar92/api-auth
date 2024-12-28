<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class TenantsSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear un tenant
        $tenant = Tenant::create([
            'name' => 'Acme Inc.',
            'domain' => 'acme.mis-saas.com',
            'plan' => 'pro',
        ]);

        // 2. Crear un usuario "Admin" dentro de Acme Inc.
        $adminUser = User::factory()->create([
            'name' => 'Acme Admin',
            'email' => 'admin@acme.com',
            'tenant_id' => $tenant->id,
            'password' => bcrypt('admin123'), // define la contraseña
        ]);

        // 3. Asignar rol "Admin" (suponiendo que ya existe el rol en tu DB).
        $adminUser->assignRole('Admin');

        // 4. Crear un usuario "User" dentro de Acme Inc.
        $normalUser = User::factory()->create([
            'name' => 'Acme User',
            'email' => 'user@acme.com',
            'tenant_id' => $tenant->id,
            'password' => bcrypt('user123'),  // define la contraseña
        ]);
        $normalUser->assignRole('User');
    }
}
