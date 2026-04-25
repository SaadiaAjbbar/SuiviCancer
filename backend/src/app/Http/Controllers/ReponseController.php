<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\reponse_questionnaires;
use App\Models\ReponseQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReponseController extends Controller
{
    // 1. L-Patient i-chouf les questions dyal t-tbib dyalo
    public function getMyQuestions()
    {
        $patient = Auth::user()->patient;

        // Njbdou les questions dyal l-medecin lli m-affecter lih had l-patient

        $questions = Question::where('medecin_id', $patient->medecin_id)->get();

        return response()->json($questions);
    }



    // F-ReponseController.php

    public function getPatientHistory()
    {
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['message' => 'Patient introuvable'], 404);
        }

        // Njibou l-ajwiba b-les détails dyalhom o les questions li m3ahom
        $history = Reponse::with('reponse_questionnaires.question')
            ->where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc') // Bach n-choufou l-jdid houwa l-luwel
            ->get();

        return response()->json($history);
    }
    // 2. L-Patient i-sift l-ajwiba dyalo
    public function storeReponses(Request $request)
    {
        // 1. Validation
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.reponse' => 'required|string',
        ]);

        $patient = Auth::user()->patient;

        // 2. Créer l'entrée principale de la réponse
        $reponsePrincipale = Reponse::create([
            'patient_id' => $patient->id,
        ]);

        // 3. Sauvegarder chaque question/réponse
        foreach ($request->responses as $respData) {
            // Remplacez 'reponse_questionnaires' par votre nom de modèle exact
            \App\Models\reponse_questionnaires::create([
                'reponses_id'  => $reponsePrincipale->id, // Liaison avec l'ID parent
                'questions_id' => $respData['question_id'],
                'reponse'      => $respData['reponse'],
            ]);
        }

        return response()->json([
            'message' => 'Réponses enregistrées avec succès !',
        ], 201);
    }
    //hada khasena nsayebohe
    // App/Http/Controllers/ReponseController.php

    public function getRponses()
    {
        $medecin = Auth::user()->medecin;

        if (!$medecin) {
            return response()->json(['message' => 'Medecin introuvable'], 404);
        }

        // N-jbdou l-ajwiba dial l-patients lli taba3in l had l-medecin
        $reponses = Reponse::with([
            'patient.user',
            'reponse_questionnaires.question',
            'etat_general.rendezVous',
            'etat_general.traitement',
            'etat_general.conseil'
        ])
            ->whereHas('patient', function ($query) use ($medecin) {
                $query->where('medecin_id', $medecin->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reponses);
    }

    // Modifier une session de réponse complète
    public function updateReponses(Request $request, $id)
    {
        $patient = Auth::user()->patient;
        $reponsePrincipale = Reponse::where('id', $id)->where('patient_id', $patient->id)->firstOrFail();

        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.reponse' => 'required|string',
        ]);

        // On met à jour chaque détail
        foreach ($request->responses as $respData) {
            \App\Models\reponse_questionnaires::updateOrCreate(
                ['reponses_id' => $reponsePrincipale->id, 'questions_id' => $respData['question_id']],
                ['reponse' => $respData['reponse']]
            );
        }

        return response()->json(['message' => 'Réponses mises à jour !']);
    }

    // Supprimer une session de réponse
    public function destroyReponse($id)
    {
        $patient = Auth::user()->patient;
        $reponse = Reponse::where('id', $id)->where('patient_id', $patient->id)->firstOrFail();

        // Les reponse_questionnaires seront supprimés automatiquement si vous avez configuré le "onDelete cascade"
        // Sinon, supprimez-les manuellement avant :
        \App\Models\reponse_questionnaires::where('reponses_id', $reponse->id)->delete();
        $reponse->delete();

        return response()->json(['message' => 'Réponse supprimée']);
    }
}
