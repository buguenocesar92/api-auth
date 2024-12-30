<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class Tenant extends Model
{
    protected $fillable = ['name', 'domain', 'database']; // Agrega los campos que pueden ser asignados masivamente

    protected static function booted()
    {
        static::created(function ($tenant) {
            // Crear automáticamente la base de datos del tenant
            $databaseName = $tenant->database;

            try {
                DB::statement("CREATE DATABASE `$databaseName`");

                // Ejecutar migraciones para este tenant
                $tenant->makeCurrent(); // Cambia al tenant actual
                Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
                $tenant->forgetCurrent(); // Vuelve al estado global
            } catch (\Exception $e) {
                throw new \Exception("Failed to create database: {$e->getMessage()}");
            }
        });
    }
}
