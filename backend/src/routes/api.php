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

// Routes Protégées (khass l'Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:ADMINGLOBAL'])->group(function () {
    Route::post('/hopitaux', [HopitalController::class, 'store']);
    Route::put('/hopitaux/{id}', [HopitalController::class, 'update']); // Modifier hopital
    Route::delete('/hopitaux/{id}', [HopitalController::class, 'destroy']); // Supprimer hopital
    Route::get('/hopitaux', [HopitalController::class, 'index']); //liste des hopitaux
    Route::post('/admins-hopital', [AdminController::class, 'createAdminHopital']); // Création et Affectation de l'Admin Hopital

});


Route::middleware(['auth:sanctum', 'role:ADMINHOPITAL'])->group(function () {
    // CRUD toxicites
    Route::post('/toxicites', [ToxiciteController::class, 'store']); //ajouter toxicite
    Route::get('/toxicites', [ToxiciteController::class, 'index']);      // Voir tout toxicite
    Route::put('/toxicites/{id}', [ToxiciteController::class, 'update']); // Modifier toxicite
    Route::delete('/toxicites/{id}', [ToxiciteController::class, 'destroy']); // Supprimer toxicite

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
    Route::apiResource('patients', PatientController::class);
});

Route::middleware(['auth:sanctum', 'role:MEDECIN'])->group(function () {
    // Route bach t-lister o t-creeyi
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::post('/consultations', [ConsultationController::class, 'store']);

    // Route bach tchoufi we7da bo7dha
    Route::get('/consultations/{id}', [ConsultationController::class, 'show']);

    // Route dyal l-Update (PUT awla PATCH)
    Route::put('/consultations/{id}', [ConsultationController::class, 'update']);

    // Route dyal Delete
    Route::delete('/consultations/{id}', [ConsultationController::class, 'destroy']);


    Route::post('/etat-general', [EtatGeneralController::class, 'store']);
    Route::get('/etat-general/consultation/{consultation_id}', [EtatGeneralController::class, 'showByConsultation']);
    Route::put('/etat-general/{id}', [EtatGeneralController::class, 'update']);
    Route::delete('/etat-general/{id}', [EtatGeneralController::class, 'destroy']);

    Route::apiResource('questions', QuestionController::class);

    Route::apiResource('rendez-vous', RendezVousController::class);
    Route::apiResource('traitements', TraitementController::class);
    Route::apiResource('conseils', ConseilController::class);
});

Route::middleware(['auth:sanctum', 'role:PATIENT'])->group(function () {
    // Bach i-chouf l-as'ila
    Route::get('/my-questions', [ReponseController::class, 'getMyQuestions']);
    // Bach i-jawb
    Route::post('/submit-responses', [ReponseController::class, 'storeReponses']);
});

Route::get('/responses' , [ReponseController::class, 'getRponses'])->middleware('auth:sanctum');
