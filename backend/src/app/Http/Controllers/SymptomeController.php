<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Toxicite;
use App\Models\AdminHopital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Symptome;

class SymptomeController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'toxicite_id' => 'required|exists:toxicites,id',
        ]);

        // 2. Check de sécurité: Wach had la toxicité dyal had l'hopital?
        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php

        $toxicite = Toxicite::where('id', $request->toxicite_id)
            ->where('hopital_id', $adminHopital->hopital_id)
            ->first();

        if (!$toxicite) {
            return response()->json([
                'message' => "Hadi machi toxicité dyal l'hôpital dyalkom!"
            ], 403);
        }

        // 3. Création dyal Symptôme
        $symptome = Symptome::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'toxicite_id' => $request->toxicite_id
        ]);

        return response()->json([
            'message' => 'Symptôme ajouté avec succès',
            'data' => $symptome
        ], 201);
    }

    // 1. Modifier un Symptôme
    public function update(Request $request, $id)
    {
        $adminHopital = Auth::user()->adminHopital;

        // Jbed l'symptôme o t'akked men l'appartenance via la toxicité
        $symptome = Symptome::whereHas('toxicite', function ($query) use ($adminHopital) {
            $query->where('hopital_id', $adminHopital->hopital_id);
        })->find($id);

        if (!$symptome) {
            return response()->json(['message' => 'Symptôme introuvable ou accès non autorisé'], 404);
        }

        $request->validate([
            'nom' => 'string|max:255',
            'description' => 'nullable|string',
            'toxicite_id' => 'exists:toxicites,id'
        ]);

        $symptome->update($request->all());

        return response()->json([
            'message' => 'Symptôme modifié avec succès',
            'data' => $symptome
        ]);
    }

    // 2. Supprimer un Symptôme
    public function destroy($id)
    {
        $adminHopital = Auth::user()->adminHopital;

        $symptome = Symptome::whereHas('toxicite', function ($query) use ($adminHopital) {
            $query->where('hopital_id', $adminHopital->hopital_id);
        })->find($id);

        if (!$symptome) {
            return response()->json(['message' => 'Symptôme introuvable ou accès non autorisé'], 404);
        }

        $symptome->delete();

        return response()->json(['message' => 'Symptôme supprimé avec succès']);
    }

    // 3. Afficher les détails d'un seul symptôme (Show)
    public function show($id)
    {
        $adminHopital = Auth::user()->adminHopital;

        $symptome = Symptome::whereHas('toxicite', function ($query) use ($adminHopital) {
            $query->where('hopital_id', $adminHopital->hopital_id);
        })->with('toxicite')->find($id);

        if (!$symptome) {
            return response()->json(['message' => 'Symptôme introuvable'], 404);
        }

        return response()->json($symptome);
    }
}
