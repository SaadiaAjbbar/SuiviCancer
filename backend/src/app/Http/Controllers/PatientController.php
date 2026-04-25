<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Infermier;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Helper pour récupérer les infos pro (Infirmier ou Medecin) de l'utilisateur connecté
     */
    private function getProInfo($user)
    {
        if ($user->role === 'INFIRMIERE') {
            return Infermier::where('user_id', $user->id)->first();
        }
        if ($user->role === 'MEDECIN') {
            return Medecin::where('user_id', $user->id)->first();
        }
        return null;
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'date_naissance' => 'required|date',
            'sexe' => 'required|string',
            'telephone' => 'required|string',
            'medecin_id' => 'required|exists:medecins,id'
        ]);

        $userLogui = Auth::user();
        // Seule l'infirmière crée le patient selon ton api.php
        $proInfo = $this->getProInfo($userLogui);

        if (!$proInfo) {
            return response()->json(['message' => "Accès refusé. Profil pro introuvable."], 403);
        }

        // Vérification: Le médecin appartient-il au même hôpital ?
        $medecin = Medecin::where('id', $request->medecin_id)
            ->where('hopital_id', $proInfo->hopital_id)
            ->first();

        if (!$medecin) {
            return response()->json([
                'message' => "Le médecin sélectionné n'appartient pas à votre hôpital."
            ], 422);
        }

        return DB::transaction(function () use ($request, $proInfo) {
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->password), // Vérifie que c'est 'mot_de_passe' ou 'password' dans ton model
                'role' => 'PATIENT',
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'date_naissance' => $request->date_naissance,
                'sexe' => $request->sexe,
                'telephone' => $request->telephone,
                'hopital_id' => $proInfo->hopital_id,
                'medecin_id' => $request->medecin_id,
            ]);

            return response()->json([
                'message' => 'Patient créé avec succès',
                'data' => $patient->load('user')
            ], 201);
        });
    }

    public function index()
    {
        $userLogui = Auth::user();
        $proInfo = $this->getProInfo($userLogui);

        if (!$proInfo) {
            return response()->json(['message' => 'Profil non autorisé'], 403);
        }

        // On liste tous les patients de l'hôpital du pro connecté
        $patients = Patient::where('hopital_id', $proInfo->hopital_id)
            ->with(['user', 'medecin.user'])
            ->get();

        return response()->json($patients);
    }


    public function getMedecinPatients()
    {
        $userLogui = Auth::user();
        $proInfo = $this->getProInfo($userLogui);

        // Vérification: Khass darori ikoun Medecin
        if (!$proInfo || $userLogui->role !== 'MEDECIN') {
            return response()->json(['message' => 'Accès réservé aux médecins'], 403);
        }

        // Njibo ghir les patients li 3ndhom id_medecin dyal had l-proInfo
        $patients = Patient::where('medecin_id', $proInfo->id)
            ->with(['user']) // Kan-loadiv ghir l-user hit l-medecin rah 3arfinah chkon
            ->get();

        return response()->json($patients);
    }
    public function show($id)
    {
        $userLogui = Auth::user();
        $proInfo = $this->getProInfo($userLogui);

        if (!$proInfo) return response()->json(['message' => 'Accès refusé'], 403);

        $patient = Patient::where('id', $id)
            ->where('hopital_id', $proInfo->hopital_id)
            ->with(['user', 'medecin.user'])
            ->firstOrFail();

        return response()->json($patient);
    }

    public function update(Request $request, $id)
    {
        $userLogui = Auth::user();
        $proInfo = $this->getProInfo($userLogui);

        if (!$proInfo) return response()->json(['message' => 'Accès refusé'], 403);

        $patient = Patient::where('id', $id)
            ->where('hopital_id', $proInfo->hopital_id)
            ->firstOrFail();

        $request->validate([
            'nom' => 'string',
            'prenom' => 'string',
            'email' => 'email|unique:users,email,' . $patient->user_id,
            'date_naissance' => 'date',
            'sexe' => 'string',
            'telephone' => 'string',
            'medecin_id' => 'exists:medecins,id'
        ]);

        return DB::transaction(function () use ($request, $patient) {
            // Mise à jour de l'User lié
            if ($request->hasAny(['nom', 'prenom', 'email'])) {
                $patient->user->update($request->only(['nom', 'prenom', 'email']));
            }

            // Mise à jour du Patient
            $patient->update($request->only([
                'date_naissance',
                'sexe',
                'telephone',
                'medecin_id'
            ]));

            return response()->json([
                'message' => 'Patient mis à jour avec succès',
                'data' => $patient->load(['user', 'medecin.user'])
            ]);
        });
    }

    public function destroy($id)
    {
        $userLogui = Auth::user();
        $proInfo = $this->getProInfo($userLogui);

        if (!$proInfo) return response()->json(['message' => 'Accès refusé'], 403);

        $patient = Patient::where('id', $id)
            ->where('hopital_id', $proInfo->hopital_id)
            ->firstOrFail();

        // Supprimer l'utilisateur (le patient suivra via le onDelete cascade ou manuellement)
        $patient->user->delete();

        return response()->json(['message' => 'Patient supprimé avec succès']);
    }
}
