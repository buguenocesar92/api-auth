<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function assignAdmin(Request $request)
    {
        // Validar que venga un user_id o user_email
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Buscar al usuario
        $user = User::findOrFail($request->user_id);

        // Asignar el rol "admin"
        $user->assignRole('admin');

        return response()->json([
            'message' => "User {$user->name} has been assigned the 'admin' role.",
            'user' => $user
        ]);
    }

}