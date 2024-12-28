<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usercontroller;
use App\Http\Controllers\TenantRegistrationController;
Route::group([
    //'middleware' => 'api',
    'prefix' => 'auth',
    //'middleware' => ['auth:api', 'role:Super-Admin'],
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); //->middleware('auth:api')
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});


// routes/api.php (o donde manejes tus rutas)

Route::group([
    'middleware' => ['auth:api', 'role:Super-Admin'], // o 'role:admin' si admin puede crear otro admin
], function () {
    Route::post('/assign-admin', [UserController::class, 'assignAdmin']);
});

Route::post('/tenant/register-user', [TenantRegistrationController::class, 'registerUserInTenant'])
    ->name('tenant.register-user');
