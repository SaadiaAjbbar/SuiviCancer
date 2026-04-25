<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminHopital;
use App\Models\User;
use App\Models\Hopital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function createAdminHopital(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'mot_de_passe' => 'required|min:8',
            'hopital_id' => 'required|exists:hopitals,id|unique:admin_hopitals,hopital_id',
        ]);

        // 1. Créer l'User awel 7aja
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role' => 'ADMINHOPITAL',
        ]);

        // 2. Créer l'entrée f'la table AdminHopital (L'affectation)
        AdminHopital::create([
            'user_id' => $user->id,
            'hopital_id' => $request->hopital_id
        ]);

        return response()->json(['message' => 'Admin Hôpital créé et lié avec succès'], 201);
    }

    // f App\Http\Controllers\AuthController.php (aw ay controller dyal l-authentification)
    public function logout(Request $request)
    {
        // Kan-ms7ou l-token li khdam bih l-user daba
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnecté avec succès'
        ]);
    }
}
