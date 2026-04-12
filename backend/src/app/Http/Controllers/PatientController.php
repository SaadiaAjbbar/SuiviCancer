<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'date_naissance' => 'required|date',
            'sexe' => 'required|string',
            'telephone' => 'required|string',
            // medecin_id darouri o khasso i'koun f table medecins
            'medecin_id' => 'required|exists:medecins,id'
        ]);

        // 2. Njbdou l'hopital_id dyal l'infermier m'logui
        $userLogui = Auth::user();
        $infermierInfo = Infermier::where('user_id', $userLogui->id)->first();

        if (!$infermierInfo) {
            return response()->json(['message' => "Accès refusé"], 403);
        }

        // 3. Vérification: Wach dak l'medecin kayntami l'nefs l'hopital?
        $medecin = Medecin::where('id', $request->medecin_id)
            ->where('hopital_id', $infermierInfo->hopital_id)
            ->first();

        if (!$medecin) {
            return response()->json([
                'message' => "Le médecin sélectionné n'appartient pas à votre hôpital."
            ], 422);
        }

        return DB::transaction(function () use ($request, $infermierInfo) {
            // 4. Création de l'User (Patient)
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->password),
                'role' => 'PATIENT',
            ]);

            // 5. Création du Patient avec affectation au médecin
            $patient = Patient::create([
                'user_id' => $user->id,
                'date_naissance' => $request->date_naissance,
                'sexe' => $request->sexe,
                'telephone' => $request->telephone,
                'hopital_id' => $infermierInfo->hopital_id,
                'medecin_id' => $request->medecin_id, // L'affectation hna
            ]);

            return response()->json([
                'message' => 'Patient créé et affecté au médecin avec succès',
                'data' => $patient->load('user')
            ], 201);
        });
    }

    // 1. Afficher la liste des patients de l'hôpital
    public function index()
    {
        $userLogui = Auth::user();
        // Njbdou l'hopital dyal l'infirmier
        $infermierInfo = Infermier::where('user_id', $userLogui->id)->firstOrFail();

        $patients = Patient::where('hopital_id', $infermierInfo->hopital_id)
            ->with(['user', 'medecin.user']) // Njibo m3ana info dyal l-patient o t-tbib dyalo
            ->get();

        return response()->json($patients);
    }

    // 2. Modifier un patient
    public function update(Request $request, $id)
    {
        $userLogui = Auth::user();
        $infermierInfo = Infermier::where('user_id', $userLogui->id)->firstOrFail();

        // Check wach l-patient dyal had l-hôpital
        $patient = Patient::where('id', $id)
            ->where('hopital_id', $infermierInfo->hopital_id)
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
            // Update table 'users'
            $patient->user->update($request->only(['nom', 'prenom', 'email']));

            // Update table 'patients'
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

    // 3. Voir les détails d'un seul patient
    public function show($id)
    {
        $userLogui = Auth::user();
        $infermierInfo = Infermier::where('user_id', $userLogui->id)->firstOrFail();

        $patient = Patient::where('id', $id)
            ->where('hopital_id', $infermierInfo->hopital_id)
            ->with(['user', 'medecin.user'])
            ->firstOrFail();

        return response()->json($patient);
    }

    // 4. Supprimer un patient
    public function destroy($id)
    {
        $userLogui = Auth::user();
        $infermierInfo = Infermier::where('user_id', $userLogui->id)->firstOrFail();

        $patient = Patient::where('id', $id)
            ->where('hopital_id', $infermierInfo->hopital_id)
            ->firstOrFail();

        // Cascade delete ghadi i-m7i l-ligne f table 'patients' automatique
        $patient->user->delete();

        return response()->json(['message' => 'Patient supprimé avec succès']);
    }
    // Lister les patients de l'hôpital (pour l'infirmier)

}
