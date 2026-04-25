<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MedecinController extends Controller
{
    
    public function index()
    {
        $user = Auth::user();
        if ($user->role == "ADMINHOPITAL") {
            $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php


            $medecins = Medecin::where('hopital_id', $adminHopital->hopital_id)
                ->with('user')
                ->get();
            return response()->json($medecins);
        }else if($user->role == "INFIRMIERE"){
             $infermier = $user->infermier; // Khass t'koun derti la relation f User.php


            $medecins = Medecin::where('hopital_id', $infermier->hopital_id)
                ->with('user')
                ->get();
            return response()->json($medecins);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'specialite' => 'required|string',
        ]);

        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php


        return DB::transaction(function () use ($request, $adminHopital) {
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->password),
                'role' => 'MEDECIN', // T'akkedi t'zidiha f Check Constraint f PGAdmin!
            ]);

            $medecin = Medecin::create([
                'user_id' => $user->id,
                'hopital_id' => $adminHopital->hopital_id,
                'specialite' => $request->specialite
            ]);

            return response()->json(['message' => 'Médecin créé avec succès', 'data' => $medecin->load('user')], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php

        $medecin = Medecin::where('id', $id)->where('hopital_id', $adminHopital->hopital_id)->firstOrFail();

        $request->validate([
            'email' => 'email|unique:users,email,' . $medecin->user_id,
        ]);

        $medecin->user->update($request->only(['nom', 'prenom', 'email']));
        $medecin->update($request->only(['specialite']));

        return response()->json(['message' => 'Médecin modifié', 'data' => $medecin->load('user')]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $adminHopital = $user->adminHopital; // Khass t'koun derti la relation f User.php

        $medecin = Medecin::where('id', $id)->where('hopital_id', $adminHopital->hopital_id)->firstOrFail();

        $medecin->user->delete(); // Cascade m7i l'profil medecin automatique
        return response()->json(['message' => 'Médecin supprimé']);
    }
}
