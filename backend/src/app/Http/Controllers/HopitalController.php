<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hopital;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HopitalController extends Controller
{


    public function store(Request $request)
    {
        // 1. Validation des données
        $validator = Validator::make($request->all(), [
            'nom'       => 'required|string|max:255',
            'adresse'   => 'required|string',
            'telephone' => 'required|string|unique:hopitals',
            'email'     => 'required|email|unique:hopitals',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Création de l'hôpital
        $hopital = Hopital::create([
            'nom'       => $request->nom,
            'adresse'   => $request->adresse,
            'telephone' => $request->telephone,
            'email'     => $request->email,
        ]);

        // 3. Réponse
        return response()->json([
            'message' => 'Hôpital créé avec succès',
            'data'    => $hopital
        ], 201);
    }

    public function index(){

    $all_hopitaux=Hopital::all();
        return response()->json([
            "liste"=>$all_hopitaux
        ]);
    }
    public function update(Request $request, $id)
    {
        $hopital = Hopital::find($id);

        if (!$hopital) {
            return response()->json(['message' => 'Hôpital introuvable'], 404);
        }

        // Validation dyal les données
        $request->validate([
            'nom'       => 'string|max:255',
            'adresse'   => 'string',
            'telephone' => 'string|unique:hopitals,telephone,' . $id,
            'email'     => 'email|unique:hopitals,email,' . $id,
        ]);

        $hopital->update($request->all());

        return response()->json([
            'message' => 'Hôpital modifié avec succès',
            'data'    => $hopital
        ], 200);
    }

    // 2. Supprimer un hôpital
    public function destroy($id)
    {
        $hopital = Hopital::find($id);

        if (!$hopital) {
            return response()->json(['message' => 'Hôpital introuvable'], 404);
        }

        try {
            $hopital->delete();
        } catch (QueryException $e) {
            // SQLSTATE 23000/23503 indicates FK constraint violation (dependent records exist)
            if (in_array($e->getCode(), ['23000', '23503'])) {
                return response()->json([
                    'message' => 'Impossible de supprimer cet hôpital car il est lié à d\'autres enregistrements.'
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Hôpital supprimé avec succès'
        ], 200);
    }
}
