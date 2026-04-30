<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'gravite' => 'required|string',
            'patient_id' => 'required|exists:patients,id',
            'toxicite_ids' => 'required|array', // Array dyal les IDs dyal les toxicités
            'symptome_ids' => 'required|array', // Array dyal les IDs dyal les symptômes
        ]);

        // 1. Njbdou l-profil dyal l-medecin m-logui

        $user = Auth::user();
        $medecin = $user->medecin;

        if (!$medecin) {
            return response()->json([
                'message' => 'Profil médecin introuvable pour cet utilisateur.',
                'user_id_logged' => $user->id
            ], 404);
        }
        // 2. Vérification: Wach l-patient tabe3 l had l-medecin?
        $patient = Patient::where('id', $request->patient_id)
            ->where('medecin_id', $medecin->id)
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'Ce patient ne fait pas partie de votre liste.'], 403);
        }

        return DB::transaction(function () use ($request, $medecin) {
            // 3. Créer la consultation
            $consultation = Consultation::create([
                'date' => $request->date,
                'gravite' => $request->gravite,
                'patient_id' => $request->patient_id,
                'medecin_id' => $medecin->id,
            ]);

            // 4. Attach les Toxicités o les Symptômes (Pivot tables)
            $consultation->toxicites()->attach($request->toxicite_ids);
            $consultation->symptomes()->attach($request->symptome_ids);

            return response()->json([
                'message' => 'Consultation enregistrée avec succès',
                'data' => $consultation->load(['toxicites', 'symptomes'])
            ], 201);
        });
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $medecin = Medecin::where('user_id', $user->id)->first();

        if (!$medecin) {
            return response()->json(['message' => 'Profil médecin introuvable'], 404);
        }

        $consultation = Consultation::where('id', $id)
            ->where('medecin_id', $medecin->id)
            ->first();

        if (!$consultation) {
            return response()->json(['message' => 'Consultation introuvable'], 404);
        }

        try {
            return DB::transaction(function () use ($consultation) {
                // 1. N-m7iw l-3alaqat f les tables pivot (Détachage)
                // Had l-khoutwa darouriya bach Database mat-bloquich
                $consultation->symptomes()->detach();
                $consultation->toxicites()->detach();

                // 2. N-m7iw l-consultation daba mnin wellat "bo7dha"
                $consultation->delete();

                return response()->json(['message' => 'Consultation supprimée avec succès']);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $medecin = $user->medecin;

        if (!$medecin) {
            return response()->json([
                'message' => 'Profil médecin introuvable pour cet utilisateur.',
                'user_id_logged' => $user->id
            ], 404);
        }
        // Check wach l-consultation dyal had t-tbib
        $consultation = Consultation::where('id', $id)
            ->where('medecin_id', $medecin->id)
            ->firstOrFail();

        $request->validate([
            'date' => 'date',
            'gravite' => 'string',
            'toxicite_ids' => 'array',
            'symptome_ids' => 'array',
        ]);

        return DB::transaction(function () use ($request, $consultation) {
            // 1. Update les infos de base
            $consultation->update($request->only(['date', 'gravite']));

            // 2. Sync les relations (Pivot tables)
            if ($request->has('toxicite_ids')) {
                $consultation->toxicites()->sync($request->toxicite_ids);
            }

            if ($request->has('symptome_ids')) {
                $consultation->symptomes()->sync($request->symptome_ids);
            }

            return response()->json([
                'message' => 'Consultation mise à jour avec succès',
                'data' => $consultation->load(['toxicites', 'symptomes'])
            ]);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $medecin = $user->medecin;


        if (!$medecin) {
            return response()->json([
                'message' => 'Profil médecin introuvable pour cet utilisateur.',
                'user_id_logged' => $user->id
            ], 404);
        }
        // Afficher ghir les consultations dyal had l-medecin
        $consultations = Consultation::where('medecin_id', $medecin->id)
            ->with(['patient.user', 'toxicites', 'symptomes', 'etatGeneral']) // Zid 'etatGeneral' hna
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($consultations);
    }

    public function getPatientConsultations()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Profil patient non trouvé'], 404);
        }

        $consultations = Consultation::where('patient_id', $patient->id)
            ->with([
                'medecin.user',
                'toxicites',
                'symptomes',
                'etatGeneral.traitement',
                'etatGeneral.rendezVous',
                'etatGeneral.conseil'
            ])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($consultations);
    }
}
