<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Infermier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InfermierController extends Controller
{

    // 1. Afficher toutes les infirmières de l'hôpital de l'admin
    public function index()
    {
        $user = Auth::user();
        $adminHopital = $user->adminHopital;

        $infermiers = Infermier::where('hopital_id', $adminHopital->hopital_id)
            ->with('user')
            ->get();
        return response()->json($infermiers);
    }

    // 2. Ajouter une infirmière
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'mot_de_passe' => 'required|min:8',
        ]);

        $user = Auth::user();
        $adminHopital = $user->adminHopital; 

        return DB::transaction(function () use ($request, $adminHopital) {
            // Créer l'User
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->mot_de_passe),
                'role' => 'INFIRMIERE',
            ]);

            // Créer le profil Infermier
            $infermier = Infermier::create([
                'user_id' => $user->id,
                'hopital_id' => $adminHopital->hopital_id
            ]);

            return response()->json(['message' => 'Infirmière créée avec succès', 'data' => $infermier->load('user')], 201);
        });
    }

    // 3. Modifier une infirmière
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php

        $infermier = Infermier::where('id', $id)->where('hopital_id', $adminHopital->hopital_id)->firstOrFail();

        $request->validate([
            'nom' => 'string',
            'prenom' => 'string',
            'email' => 'email|unique:users,email,' . $infermier->user_id,
        ]);

        $user = $infermier->user;
        $user->update($request->only(['nom', 'prenom', 'email']));

        return response()->json(['message' => 'Infirmière modifiée', 'data' => $infermier->load('user')]);
    }

    // 4. Supprimer une infirmière
    public function destroy($id)
    {
        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php

        $infermier = Infermier::where('id', $id)->where('hopital_id', $adminHopital->hopital_id)->firstOrFail();

        // Delete user (cascade ghadi tm7i l'infermier table automatique)
        $infermier->user->delete();

        return response()->json(['message' => 'Infirmière supprimée avec succès']);
    }
}
