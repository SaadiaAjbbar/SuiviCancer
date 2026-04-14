<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EtatGeneral;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatGeneralController extends Controller
{
    // 1. Ajouter un état général pour une consultation
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'consultation_id' => 'nullable|exists:consultations,id',
            'reponses_id' => 'nullable|exists:reponses,id',
        ]);
        $user = Auth::user();
        $medecin = $user->medecin;



        $etat = EtatGeneral::create([
            'description' => $request->description,
            'consultation_id' => $request->consultation_id,
            'reponses_id' => $request->reponses_id,
        ]);

        return response()->json(['message' => 'État général enregistré', 'data' => $etat], 201);
    }

    // 2. Afficher l'état général d'une consultation précise
    public function showByConsultation($consultation_id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $consultation = Consultation::where('id', $consultation_id)->where('medecin_id', $medecin->id)->firstOrFail();

        $etat = EtatGeneral::where('consultation_id', $consultation_id)->first();

        if (!$etat) {
            return response()->json(['message' => 'Aucun état général pour cette consultation'], 404);
        }

        return response()->json($etat);
    }

    // 3. Modifier l'état général
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $etat = EtatGeneral::where('medecin_id', $medecin->id)->findOrFail($id);

        $request->validate(['description' => 'required|string']);
        $etat->update($request->only('description'));

        return response()->json(['message' => 'État général mis à jour', 'data' => $etat]);
    }

    // 4. Supprimer
    public function destroy($id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $etat = EtatGeneral::whereHas('consultation', function ($query) use ($medecin) {
            $query->where('medecin_id', $medecin->id);
        })->findOrFail($id);

        $etat->delete();
        return response()->json(['message' => 'État général supprimé']);
    }
}
