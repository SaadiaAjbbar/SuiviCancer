<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Question;
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

    // 2. L-Patient i-sift l-ajwiba dyalo
    public function storeReponses(Request $request)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.reponse' => 'required|string',
        ]);

        $patient = Auth::user()->patient;
        $savedResponses = [];

        foreach ($request->responses as $respData) {
            $savedResponses[] = ReponseQuestionnaire::create([
                'patient_id' => $patient->id,
                'question_id' => $respData['question_id'],
                'reponse' => $respData['reponse'],
            ]);
        }

        return response()->json([
            'message' => 'Vos réponses ont été enregistrées avec succès',
            'data' => $savedResponses
        ], 201);
    }
}
