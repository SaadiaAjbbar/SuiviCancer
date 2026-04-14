<?php

namespace App\Http\Controllers;


use App\Models\RendezVous;
use App\Models\EtatGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RendezVousController extends Controller
{
    // 1. Lister les RDV dyal les patients dyal had t-tbib
    public function index()
    {
        $medecin = Auth::user()->medecin;

        $rdvs = RendezVous::whereHas('patient', function ($query) use ($medecin) {
            $query->where('medecin_id', $medecin->id);
        })->with('patient.user', 'etatGeneral')->get();

        return response()->json($rdvs);
    }

    // 2. Créer un RDV basé sur un Etat General
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:now',
            'etat_general_id' => 'required|exists:etat_generals,id',
        ]);

        $medecin = Auth::user()->medecin;

        // T'akkedi beli had l-Etat General dyal patient taba3 l had t-tbib
        $etat = EtatGeneral::with('consultation', 'reponse.patients')
            ->findOrFail($request->etat_general_id);

        // Njbdou l-patient ID (soit men consultation soit men reponse)
        $patientId = $etat->consultation ? $etat->consultation->patient_id : $etat->reponse->patients_id;

        $rdv = RendezVous::create([
            'date' => $request->date,
            'status' => 'Programmé',
            'etat_general_id' => $request->etat_general_id,
            'patient_id' => $patientId
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
}
