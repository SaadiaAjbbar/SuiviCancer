<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EtatGeneral;
use App\Models\Consultation;
use App\Models\Reponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtatGeneralController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'consultation_id' => 'exists:consultations,id',
            'reponse_id' => 'exists:reponses,id',
        ]);

        if ($request->consultation_id) {
            $consultation = Consultation::find($request->consultation_id);
            if (!$consultation || $consultation->medecin_id !== Auth::user()->medecin->id) {
                return response()->json(['message' => 'Consultation non trouvée ou accès refusé'], 403);
            }
        } else if ($request->reponse_id) {
            $reponse = Reponse::find($request->reponse_id);
            if (!$reponse || $reponse->patient->medecin_id !== Auth::user()->medecin->id) {
                return response()->json(['message' => 'Réponse non trouvée ou accès refusé'], 403);
            }
        } else {
            return response()->json(['message' => 'Consultation_id ou reponse_id requis'], 400);
        }


        $etat = EtatGeneral::create([
            'description' => $request->description,
            'consultation_id' => $request->consultation_id,
            'reponse_id' => $request->reponse_id,
        ]);

        return response()->json(['message' => 'État général enregistré', 'data' => $etat], 201);
    }

    public function showByConsultation($consultation_id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $consultation = Consultation::where('id', $consultation_id)->where('medecin_id', $medecin->id)->firstOrFail();

        $etat = EtatGeneral::where('consultation_id', $consultation_id)->with(['traitement', 'rendezVous', 'conseil'])->first();

        if (!$etat) {
            return response()->json(['message' => 'Aucun état général pour cette consultation'], 404);
        }

        return response()->json($etat);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $etat = EtatGeneral::where('id', $id);

        $request->validate(['description' => 'required|string']);
        $etat->update($request->only('description'));

        return response()->json(['message' => 'État général mis à jour', 'data' => $etat]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        $etat = EtatGeneral::where('id', $id);

        $etat->delete();
        return response()->json(['message' => 'État général supprimé']);
    }


    public function getPatientBilans()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil patient non trouvé'], 404);
        }

        $bilans = EtatGeneral::where(function ($query) use ($patient) {
            // Cas 1: Bilan via Consultation
            $query->whereHas('consultation', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })
                // Cas 2: Bilan via Réponse (Suivi à distance)
                ->orWhereHas('reponse', function ($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                });
        })
            ->with([
                'consultation.medecin.user',
                'reponse.patient.medecin.user',
                'traitement',
                'rendezVous',
                'conseil'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bilans);
    }
}
