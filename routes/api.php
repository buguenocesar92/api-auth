<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usercontroller;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Http\Request;

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



Route::post('/register-tenant', [TenantRegistrationController::class, 'registerTenant']);

// Rutas "tenant aware"
Route::middleware([IdentifyTenant::class])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        // Obtiene el tenant actual desde el contenedor
        $tenant = app(\Spatie\Multitenancy\Contracts\IsTenant::class);
        $host = $request->getHost();

        if (!$tenant) {
            return response()->json(['message' => 'No tenant identified', 'host' => $host], 404);
        }

        return response()->json([
            'message' => 'Tenant identified',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
            ],
            'host' => $host,
        ]);
    });
});

