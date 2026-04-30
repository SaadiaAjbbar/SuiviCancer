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

        $user = Auth::user();
        $adminHopital = $user->adminHopital;
        $toxicite = Toxicite::where('id', $request->toxicite_id)
            ->where('hopital_id', $adminHopital->hopital_id)
            ->first();

        if (!$toxicite) {
            return response()->json([
                'message' => "cette toxicite nexeste pas dans votre hopital"
            ], 403);
        }

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

    public function index()
    {
        $user = Auth::user();
        $hopital_id = null;

        if ($user->role == "ADMINHOPITAL") {
            $adminInfo = \App\Models\AdminHopital::where('user_id', $user->id)->first();
            $hopital_id = $adminInfo?->hopital_id;
        } else if ($user->role == "MEDECIN") {
            $medecin = \App\Models\Medecin::where('user_id', $user->id)->first();
            $hopital_id = $medecin?->hopital_id;
        }

        if (!$hopital_id) {
            return response()->json(['message' => 'Accès non autorisé ou hôpital non trouvé'], 403);
        }

        $symptomes = Symptome::whereHas('toxicite', function ($query) use ($hopital_id) {
            $query->where('hopital_id', $hopital_id);
        })->with('toxicite')->get();

        return response()->json($symptomes);
    }
}
