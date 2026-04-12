<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    // 1. Lister les questions créées par ce médecin
    public function index()
    {

        $user = Auth::user();
        $medecin = $user->medecin;

        $questions = Question::where('medecin_id', $medecin->id)->get();
        return response()->json($questions);
    }

    // 2. Créer une nouvelle question (ex: "Quelle est votre température ?")
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
        ]);

         $user = Auth::user();
        $medecin = $user->medecin;


        $question = Question::create([
            'titre' => $request->titre,
            'medecin_id' => $medecin->id
        ]);

        return response()->json(['message' => 'Question ajoutée au questionnaire', 'data' => $question], 201);
    }

    // 3. Modifier une question
    public function update(Request $request, $id)
    {
         $user = Auth::user();
        $medecin = $user->medecin;

        $question = Question::where('id', $id)->where('medecin_id', $medecin->id)->firstOrFail();

        $request->validate(['titre' => 'string|max:255']);
        $question->update($request->only('titre'));

        return response()->json(['message' => 'Question modifiée', 'data' => $question]);
    }

    // 4. Supprimer une question
    public function destroy($id)
    {
         $user = Auth::user();
        $medecin = $user->medecin;

        $question = Question::where('id', $id)->where('medecin_id', $medecin->id)->firstOrFail();

        $question->delete();
        return response()->json(['message' => 'Question supprimée']);
    }
}
