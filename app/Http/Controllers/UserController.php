<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
/*     public function assignAdmin(Request $request)
    {
        // Validar que venga un user_id o user_email
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Buscar al usuario
        $user = User::findOrFail($request->user_id);

        // Asignar el rol "admin"
        $user->assignRole('Admin');

        return response()->json([
            'message' => "User {$user->name} has been assigned the 'admin' role.",
            'user' => $user
        ]);
    } */
    public function listUsersByTenant()
    {
        // Obtener usuarios del tenant
        $users = User::where('tenant_id', Auth::user()->tenant_id)->get();

        // Retornar los usuarios como respuesta JSON
        return response()->json([
            'message' => 'Usuarios obtenidos exitosamente.',
            'users' => $users
        ]);
    }

}
