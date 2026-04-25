<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConseilController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\EtatGeneralController;
use App\Http\Controllers\HopitalController;
use App\Http\Controllers\InfermierController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\SymptomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToxiciteController;
use App\Http\Controllers\TraitementController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:ADMINGLOBAL'])->group(function () {
    Route::post('/hopitaux', [HopitalController::class, 'store']);
    Route::put('/hopitaux/{id}', [HopitalController::class, 'update']);
    Route::delete('/hopitaux/{id}', [HopitalController::class, 'destroy']);
    Route::get('/hopitaux', [HopitalController::class, 'index']); 
    Route::post('/admins-hopital', [AdminController::class, 'createAdminHopital']); // Création et Affectation de l'Admin Hopital

});


Route::middleware(['auth:sanctum', 'role:ADMINHOPITAL'])->group(function () {
    // CRUD toxicites
    Route::post('/hopital/toxicites', [ToxiciteController::class, 'store']);
    Route::get('/hopital/toxicites', [ToxiciteController::class, 'index']);
    Route::put('/hopital/toxicites/{id}', [ToxiciteController::class, 'update']);
    Route::delete('/hopital/toxicites/{id}', [ToxiciteController::class, 'destroy']);

    // CRUD symptomes
    Route::post('/symptomes', [SymptomeController::class, 'store']);
    Route::get('/symptomes/{id}', [SymptomeController::class, 'show']);
    Route::put('/symptomes/{id}', [SymptomeController::class, 'update']);
    Route::delete('/symptomes/{id}', [SymptomeController::class, 'destroy']);

    // CRUD Infermiers

    Route::apiResource('infermiers', InfermierController::class);

    //crud medecins
    Route::apiResource('medecins', MedecinController::class);
});

Route::middleware(['auth:sanctum', 'role:INFIRMIERE'])->group(function () {
    // Option plus simple si apiResource te pose des soucis avec le nom du paramètre
    Route::get('infirmiers/patients', [PatientController::class, 'index']);
    Route::post('infirmiers/patients', [PatientController::class, 'store']);
    Route::get('infirmiers/patients/{id}', [PatientController::class, 'show']);
    Route::put('infirmiers/patients/{id}', [PatientController::class, 'update']);
    Route::delete('infirmiers/patients/{id}', [PatientController::class, 'destroy']);
    Route::get('infirmiers/medecins', [MedecinController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:MEDECIN'])->group(function () {
    Route::get('medecin/my-patients', [PatientController::class, 'getMedecinPatients']);
    Route::get('medecin/toxicites', [ToxiciteController::class, 'index']);      // Voir tout toxicite
    Route::get('medecin/symptomes', [SymptomeController::class, 'index']);


    // Route bach t-lister o t-creeyi
    Route::get('medecin/consultations', [ConsultationController::class, 'index']);
    Route::post('medecin/consultations', [ConsultationController::class, 'store']);

    // Route bach tchoufi we7da bo7dha
    Route::get('medecin/consultations/{id}', [ConsultationController::class, 'show']);

    // Route dyal l-Update (PUT awla PATCH)
    Route::put('medecin/consultations/{id}', [ConsultationController::class, 'update']);

    // Route dyal Delete
    Route::delete('medecin/consultations/{id}', [ConsultationController::class, 'destroy']);


    Route::post('medecin/etat-general', [EtatGeneralController::class, 'store']);
    Route::get('medecin/etat-general/consultation/{consultation_id}', [EtatGeneralController::class, 'showByConsultation']);
    Route::put('medecin/etat-general/{id}', [EtatGeneralController::class, 'update']);
    Route::delete('medecin/etat-general/{id}', [EtatGeneralController::class, 'destroy']);

    //Route::apiResource('questions', QuestionController::class);
    Route::get('medecin/questions', [QuestionController::class, 'index']);
    route::post('medecin/questions', [QuestionController::class, 'store']);
    Route::put('medecin/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('medecin/questions/{id}', [QuestionController::class, 'destroy']);


    Route::get('medecin/rendez-vous', [RendezVousController::class, 'index']);
    Route::post('medecin/rendez-vous', [RendezVousController::class, 'store']);
    Route::put('medecin/rendez-vous/{id}', [RendezVousController::class, 'update']);
    Route::delete('medecin/rendez-vous/{id}', [RendezVousController::class, 'destroy']);

    Route::get('medecin/traitements', [TraitementController::class, 'index']);
    Route::post('medecin/traitements', [TraitementController::class, 'store']);
    Route::put('medecin/traitements/{id}', [TraitementController::class, 'update']);
    Route::delete('medecin/traitements/{id}', [TraitementController::class, 'destroy']);
    // Route::apiResource('traitements', TraitementController::class);
    Route::get('medecin/conseils', [ConseilController::class, 'index']);
    Route::post('medecin/conseils', [ConseilController::class, 'store']);
    Route::put('medecin/conseils/{id}', [ConseilController::class, 'update']);
    Route::delete('medecin/conseils/{id}', [ConseilController::class, 'destroy']);
    // Route::apiResource('conseils', ConseilController::class);

    Route::get('medecin/responses', [ReponseController::class, 'getRponses']);
});

Route::middleware(['auth:sanctum', 'role:PATIENT'])->group(function () {
    // Bach i-chouf l-as'ila
    Route::get('patient/my-questions', [QuestionController::class, 'afficherQuestionsPatient']);
    // Bach i-jawb
    Route::post('patient/submit-responses', [ReponseController::class, 'storeReponses']);
    Route::get('patient/my-questions', [ReponseController::class, 'getMyQuestions']);
    Route::get('patient/my-history', [ReponseController::class, 'getPatientHistory']);

    Route::put('patient/responses/{id}', [ReponseController::class, 'updateReponses']);
    Route::delete('patient/responses/{id}', [ReponseController::class, 'destroyReponse']);
    Route::get('patient/my-rendez-vous', [RendezVousController::class, 'getPatientRDV']);
    Route::get('patient/my-traitements', [TraitementController::class, 'getPatientTraitements']);
    Route::get('patient/my-conseils', [ConseilController::class, 'getPatientConseils']);
    Route::get('patient/my-bilans', [EtatGeneralController::class, 'getPatientBilans']);
    Route::get('patient/my-consultations', [ConsultationController::class, 'getPatientConsultations']);
});
