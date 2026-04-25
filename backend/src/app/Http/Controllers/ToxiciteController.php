<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Toxicite;
use App\Models\AdminHopital;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToxiciteController extends Controller
{
    
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // 2. Récupérer l'AdminHopital lié à l'utilisateur connecté
        $user = Auth::user();

        // On va chercher l'ID de l'hôpital dans la table de liaison
        $adminInfo = AdminHopital::where('user_id', $user->id)->first();

        if (!$adminInfo) {
            return response()->json(['message' => "Vous n'êtes affecté à aucun hôpital"], 403);
        }

        // 3. Création de la toxicité
        $toxicite = Toxicite::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'hopital_id' => $adminInfo->hopital_id, // Affectation automatique
            'admin_hopital_id' => $user->id        // Qui a créé cette fiche
        ]);

        return response()->json([
            'message' => 'Toxicité ajoutée avec succès pour votre hôpital',
            'data' => $toxicite
        ], 201);
    }

    // 1. Modifier une toxicité
    public function update(Request $request, $id)
    {
        // Jbed l'admin m'logui o l'hopital dyalo
        $adminHopital = Auth::user()->adminHopital;

        // Jbed la toxicité ghir ila kant dyal had l'hopital
        $toxicite = Toxicite::where('id', $id)
            ->where('hopital_id', $adminHopital->hopital_id)
            ->first();

        if (!$toxicite) {
            return response()->json(['message' => 'Toxicité introuvable ou accès non autorisé'], 404);
        }

        // Validation
        $request->validate([
            'nom' => 'string|max:255',
            'description' => 'nullable|string',
        ]);

        $toxicite->update($request->only(['nom', 'description']));

        return response()->json([
            'message' => 'Toxicité modifiée avec succès',
            'data' => $toxicite
        ]);
    }

    // 2. Supprimer une toxicité
    public function destroy($id)
    {
        $adminHopital = Auth::user()->adminHopital;

        $toxicite = Toxicite::where('id', $id)
            ->where('hopital_id', $adminHopital->hopital_id)
            ->first();

        if (!$toxicite) {
            return response()->json(['message' => 'Toxicité introuvable ou accès non autorisé'], 404);
        }

        // Kat'supprimer la toxicité (o les symptômes ghadi itm7aw automatic ila derti cascade)
        $toxicite->delete();

        return response()->json(['message' => 'Toxicité supprimée avec succès']);
    }

    // 3. Afficher la liste des toxicités de l'hôpital (Index)

    public function index()
    {
        $user = Auth::user();

        if ($user->role == "ADMINHOPITAL") {
            // Hna khass t-checki wach la relation adminHopital kayna f User model
            $adminInfo = \App\Models\AdminHopital::where('user_id', $user->id)->first();
            if (!$adminInfo) return response()->json([], 403);

            $toxicites = Toxicite::where('hopital_id', $adminInfo->hopital_id)
                ->with('symptomes')->get();
            return response()->json($toxicites);
        } else if ($user->role == "MEDECIN") {
            // Ahmed m-logui: khassna njbdou hopital_id men la table medecins
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return response()->json(['message' => 'Medecin introuvable'], 404);
            }

            $toxicites = Toxicite::where('hopital_id', $medecin->hopital_id)
                ->with('symptomes')
                ->get();

            return response()->json($toxicites);
        }

        return response()->json([], 200);
    }
}
