<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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


Route::get('/only-admins', function () {
    return "Solo un admin ve esto.";
})->middleware(['auth:api', 'role:admin']);
