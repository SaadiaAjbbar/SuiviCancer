<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HopitalController;
use App\Http\Controllers\InfermierController;
use App\Http\Controllers\SymptomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToxiciteController;


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
