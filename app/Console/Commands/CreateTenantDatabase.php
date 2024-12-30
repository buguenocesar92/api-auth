<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CreateTenantDatabase extends Command
{
    // Nombre del comando que se usará en Artisan
    protected $signature = 'tenant:create-database {tenant_id}';

    // Descripción del comando
    protected $description = 'Creates the database for a tenant and runs migrations';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        // Obtiene el tenant_id del argumento
        $tenantId = $this->argument('tenant_id');

        // Busca el tenant en la base de datos principal
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error('Tenant not found');
            return 1; // Devuelve un código de error
        }

        try {
            // Crea la base de datos
            DB::statement("CREATE DATABASE `{$tenant->database}`");
            $this->info("Database `{$tenant->database}` created successfully.");

            // Ejecuta las migraciones en la base de datos del tenant
            $tenant->makeCurrent();
            Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
            $this->info("Migrations run successfully for `{$tenant->database}`.");
            $tenant->forgetCurrent();
        } catch (\Exception $e) {
            $this->error("Error creating database: {$e->getMessage()}");
            return 1; // Devuelve un código de error
        }

        $this->info("Tenant database and migrations completed successfully.");
        return 0; // Devuelve un código de éxito
    }
}

