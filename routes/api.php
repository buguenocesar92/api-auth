<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Controllers\UserController;

    // Rutas públicas para registro de inquilinos
    Route::group([
        'prefix' => 'tenants',
    ], function () {
        Route::post('/register', [TenantRegistrationController::class, 'registerTenant'])->name('tenants.register');
    });

    // Grupo de rutas para autenticación
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/me', [AuthController::class, 'me'])->name('me');
    });

    // Rutas para roles y permisos
    Route::middleware(['auth:api', IdentifyTenant::class])->group(function () {
        Route::group([
            'prefix' => 'roles-permissions',
            'middleware' => ['role:Admin'], // Middleware para restringir el acceso solo a roles de administrador
        ], function () {
            Route::post('/roles', [RolePermissionController::class, 'createRole'])->name('roles-permissions.create-role');
            // Listar roles y permisos
            Route::get('/roles-with-permissions', [RolePermissionController::class, 'listRolesWithPermissions'])
                ->name('roles-permissions.list-roles-with-permissions');

            // Eliminar un rol
            Route::delete('/roles/{id}', [RolePermissionController::class, 'deleteRole'])
                ->name('roles-permissions.delete-role');

            // Actualizar un rol
            Route::put('/roles/{id}', [RolePermissionController::class, 'updateRole'])
                ->name('roles-permissions.update-role');

            // Eliminar un usuario de un rol
            Route::delete('/roles/{roleId}/users/{userId}', [RolePermissionController::class, 'removeUserFromRole'])
                ->name('roles-permissions.remove-user-from-role');

            Route::delete('/roles/{roleId}/permissions', [RolePermissionController::class, 'removePermissionFromRole'])
                ->name('roles-permissions.remove-permission');

    });

    // Rutas adicionales dentro del contexto del inquilino
    Route::group([
        'prefix' => 'users',
        'middleware' => ['role:Admin'],
    ], function () {
        // Listar roles y permisos
        Route::get('/list-users-by-tenant', [UserController::class, 'listUsersByTenant'])->name('users.list-users-by-tenant');
    });
    // Rutas adicionales dentro del contexto del inquilino
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the tenant dashboard']);
    })->name('dashboard');
});
