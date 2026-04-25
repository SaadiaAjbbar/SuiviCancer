<?php

namespace App\Http\Controllers;


use App\Models\Traitement;
use App\Models\EtatGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TraitementController extends Controller
{
    public function index()
    {
        $medecin = Auth::user()->medecin;

        // Njibou les traitements dyal les patients dyal had t-tbib
        $traitements = Traitement::whereHas('patient', function ($query) use ($medecin) {
            $query->where('medecin_id', $medecin->id);
        })->with('patient.user', 'etatGeneral')->get();

        return response()->json($traitements);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
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

        // Logic bach n-jbdou patient_id automatique
        // $patientId = $etat->consultation ? $etat->consultation->patient_id : $etat->reponse->patients_id;

        $traitement = Traitement::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'etat_general_id' => $request->etat_general_id,
            'patient_id' => $patientId
        ]);

        return response()->json(['message' => 'Traitement créé avec succès', 'data' => $traitement], 201);
    }

    public function update(Request $request, $id)
    {
        $traitement = Traitement::findOrFail($id);
        $traitement->update($request->all());

        return response()->json(['message' => 'Traitement mis à jour', 'data' => $traitement]);
    }

    public function destroy($id)
    {
        $traitement = Traitement::findOrFail($id);
        $traitement->delete();
        return response()->json(['message' => 'Traitement supprimé']);
    }

    /* public function getPatientTraitements()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil patient non trouvé'], 404);
        }

        // On récupère les traitements avec les infos du médecin qui les a prescrits
        $traitements = Traitement::where('patient_id', $patient->id)
            ->with(['etatGeneral.consultation.medecin.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($traitements);
    }*/
    public function getPatientTraitements()
{
    $user = Auth::user();

    // Checki wach l-user 3ndo profil patient
    if (!$user->patient) {
        return response()->json(['message' => 'Profil patient non trouvé'], 404);
    }

    $patient_id = $user->patient->id;

    // Hna ghadi njibu ay traitement fih l-id dyal had l-patient
    // Bla ma n-diro whereHas, ghir direct where 3la l-column
    $traitements = Traitement::where('patient_id', $patient_id)
        ->with([
            'etatGeneral.consultation.medecin.user',
            'etatGeneral.reponse.patient.medecin.user'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($traitements);
}
}
