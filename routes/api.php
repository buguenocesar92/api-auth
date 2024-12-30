<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\RolePermissionController;

Route::group([
    'prefix' => 'auth',
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('auth:api');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});

Route::group([
    'prefix' => 'tenants',
], function () {
    Route::post('/register', [TenantRegistrationController::class, 'registerTenant'])->name('tenants.register');
});

Route::group([
    'prefix' => 'roles-permissions',
    'middleware' => ['auth:api'], // Middleware global para todas las rutas del grupo
], function () {
    Route::get('/roles-with-permissions', [RolePermissionController::class, 'listRolesWithPermissions'])->name('roles-permissions.list-roles-with-permissions');
    // Listar roles y permisos
    Route::get('/roles', [RolePermissionController::class, 'listRoles'])->name('roles-permissions.list-roles');
    Route::get('/permissions', [RolePermissionController::class, 'listPermissions'])->name('roles-permissions.list-permissions');

    // Crear roles y permisos
    Route::post('/roles', [RolePermissionController::class, 'createRole'])->name('roles-permissions.create-role');
    Route::post('/permissions', [RolePermissionController::class, 'createPermission'])->name('roles-permissions.create-permission');

    // Asignar roles y permisos
    Route::post('/roles/assign-to-user', [RolePermissionController::class, 'assignRoleToUser'])->name('roles-permissions.assign-role-to-user');
    Route::post('/permissions/assign-to-role', [RolePermissionController::class, 'assignPermissionToRole'])->name('roles-permissions.assign-permission-to-role');
});
