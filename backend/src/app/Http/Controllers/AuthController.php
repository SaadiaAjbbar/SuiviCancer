<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
       //return response()->json(password_hash('admin', PASSWORD_DEFAULT));
        // 1. Validation des données

        $fields = $request->validate([
            'email' => 'required|string|email',
            'mot_de_passe' => 'required|string'
        ]);

        // 2. Check email
        $user = User::where('email', $fields['email'])->first();

        // 3. Check mot_de_passe et role (AdminHopital)
        if (!$user || !Hash::check($fields['mot_de_passe'], $user->mot_de_passe)) {
            return response([
                'message' => 'votre Email ou mot de passe sont incorrect'
            ], 401);
        }

        // 4. Création de Token Sanctum
        $token = $user->createToken('adminToken')->plainTextToken;

        $responseData = [
            'user' => $user,
            'token' => $token,
            'role' => $user->role
        ];
        if ($user->role === 'ADMINHOPITAL') {
            // On récupère l'ID de l'hôpital via la relation
            $responseData['hopital_id'] = $user->adminHopital ? $user->adminHopital->hopital_id : null;
        }
        return response()->json($responseData, 200);
    }

    public function logout(Request $request)
    {
        // Supprimer l'token lli khdam bih daba
        $request->user()->currentAccessToken()->delete();

        return response(['message' => 'Logged out'], 200);
    }
}
