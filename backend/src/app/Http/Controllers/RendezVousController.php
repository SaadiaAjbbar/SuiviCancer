<?php

namespace App\Http\Controllers;


use App\Models\RendezVous;
use App\Models\EtatGeneral;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RendezVousController extends Controller
{
    public function index()
    {
        $medecin = Auth::user()->medecin;

        $rdvs = RendezVous::whereHas('patient', function ($query) use ($medecin) {
            $query->where('medecin_id', $medecin->id);
        })->with('patient.user', 'etatGeneral')->get();

        return response()->json($rdvs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:now',
            'etat_general_id' => 'required|exists:etat_generals,id',
            'patient_id' => 'required|exists:patients,id',
        ]);

            $etat = EtatGeneral::findOrFail($request->etat_general_id);
            $patient = Patient::findOrFail($request->patient_id);
            if ($etat->consultation) {
                if ($etat->consultation->medecin_id !== Auth::user()->medecin->id) {
                    return response()->json(['message' => 'État général non trouvé ou accès refusé'], 403);
                }
            } else if ($etat->reponse) {
                if ($etat->reponse->patient->medecin_id !== Auth::user()->medecin->id) {
                    return response()->json(['message' => 'État général non trouvé ou accès refusé'], 403);
                }
            } else {
                return response()->json(['message' => 'État général doit être lié à une consultation ou une réponse'], 400);
            }


        $rdv = RendezVous::create([
            'date' => $request->date,
            'status' => 'Programmé',
            'etat_general_id' => $request->etat_general_id,
            'patient_id' => $request->patient_id
        ]);

        return response()->json(['message' => 'Rendez-vous créé', 'data' => $rdv], 201);
    }

    // 3. Modifier la date ou status
    public function update(Request $request, $id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update($request->only(['date', 'status']));

        return response()->json(['message' => 'Rendez-vous mis à jour', 'data' => $rdv]);
    }

    // 4. Annuler un RDV
    public function destroy($id)
    {
        $rdv = RendezVous::findOrFail($id);
        $rdv->delete();
        return response()->json(['message' => 'Rendez-vous annulé']);
    }

    public function getPatientRDV()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil patient non trouvé'], 404);
        }

        $rdvs = RendezVous::where('patient_id', $patient->id)
            ->with(['etatGeneral.consultation.medecin.user']) 
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($rdvs);
    }
}
