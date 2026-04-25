<?php

namespace App\Http\Controllers;

use App\Models\Conseil;
use App\Models\EtatGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConseilController extends Controller
{
    public function index()
    {
        $medecin = Auth::user()->medecin;

        // Njibo les conseils dyal les patients dyal had t-tbib
        $conseils = Conseil::whereHas('patient', function ($query) use ($medecin) {
            $query->where('medecin_id', $medecin->id);
        })->with('patient.user', 'etatGeneral')->get();

        return response()->json($conseils);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'etat_general_id' => 'required|exists:etat_generals,id',
        ]);

        $etat = EtatGeneral::findOrFail($request->etat_general_id);
        $repons = EtatGeneral::find($request->etat_general_id)->reponse;

        if ($etat->consultation) {
            $patientId = $etat->consultation->patient_id;
        } else if ($repons) {
            $patientId = $repons->patient_id;
        } else {
            return response()->json(['message' => 'État général invalide, ni consultation ni réponse associée'], 400);
        }

        // N-jbdou patient_id men l-Etat General (soit consultation soit reponse)
        // $patientId = $etat->consultation ? $etat->consultation->patient_id : $etat->reponses_id;

        $conseil = Conseil::create([
            'date' => now(), // Drna l-weqt dyal daba automatique
            'description' => $request->description,
            'etat_general_id' => $request->etat_general_id,
            'patient_id' => $patientId
        ]);

        return response()->json(['message' => 'Conseil ajouté avec succès', 'data' => $conseil], 201);
    }

    public function update(Request $request, $id)
    {
        $conseil = Conseil::findOrFail($id);
        $conseil->update($request->all());

        return response()->json(['message' => 'Conseil mis à jour', 'data' => $conseil]);
    }

    public function destroy($id)
    {
        $conseil = Conseil::findOrFail($id);
        $conseil->delete();
        return response()->json(['message' => 'Conseil supprimé']);
    }
    public function getPatientConseils()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil patient non trouvé'], 404);
        }

        // On récupère les conseils avec les infos du médecin via l'Etat General
        $conseils = Conseil::where('patient_id', $patient->id)
            ->with(['etatGeneral.consultation.medecin.user'])
            ->with('etatGeneral.reponse.medecin.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($conseils);
    }
}
