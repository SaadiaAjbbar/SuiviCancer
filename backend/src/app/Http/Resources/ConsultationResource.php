<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'gravite' => $this->gravite,
            'patient_id' => $this->patient_id,
            'medecin_id' => $this->medecin_id,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'user' => $this->patient->user ? [
                        'id' => $this->patient->user->id,
                        'nom' => $this->patient->user->nom,
                        'prenom' => $this->patient->user->prenom,
                    ] : null,
                ];
            }),
            'medecin' => $this->whenLoaded('medecin', function () {
                return [
                    'id' => $this->medecin->id,
                    'specialite' => $this->medecin->specialite,
                    'user' => $this->medecin->user ? [
                        'id' => $this->medecin->user->id,
                        'nom' => $this->medecin->user->nom,
                        'prenom' => $this->medecin->user->prenom,
                    ] : null,
                ];
            }),
            'toxicites' => $this->whenLoaded('toxicites', function () {
                return $this->toxicites->map(fn($t) => [
                    'id' => $t->id,
                    'nom' => $t->nom,
                    'description' => $t->description,
                ]);
            }),
            'symptomes' => $this->whenLoaded('symptomes', function () {
                return $this->symptomes->map(fn($s) => [
                    'id' => $s->id,
                    'nom' => $s->nom,
                    'description' => $s->description,
                ]);
            }),
            'etatGeneral' => $this->whenLoaded('etatGeneral', function () {
                return $this->etatGeneral ? [
                    'id' => $this->etatGeneral->id,
                    'description' => $this->etatGeneral->description,
                ] : null;
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}