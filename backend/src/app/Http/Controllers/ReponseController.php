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

    // 2. L-Patient i-sift l-ajwiba dyalo
    public function storeReponses(Request $request)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.reponse' => 'required|string',
        ]);
        $savedResponses = [];
        $patient = Auth::user()->patient;

        $reponse = Reponse::create([
            'patient_id' => $patient->id,
        ]);


        foreach ($request->responses as $respData) {
            $savedResponses =  reponse_questionnaires::create([
                'reponses_id' => $reponse->id,
                'questions_id' => $respData['question_id'],
                'reponse' => $respData['reponse'],
            ]);
        }

         return response()->json([
                'message' => 'Responses saved successfully',
                'data' => $savedResponses
          ], 201);
    }
    //hada khasena nsayebohe
    public function getRponses()
    {
        $medecin = Auth::user()->medecin;

        $reponses = Reponse::with('reponse_questionnaires.question')
            ->whereHas('patients', function ($query) use ($medecin) {
                $query->where('medecin_id', $medecin->id);
            })
            ->get();

        return response()->json($reponses);
    }
}
