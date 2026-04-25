<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RendezVousResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'status' => $this->status,
            'etat_general_id' => $this->etat_general_id,
            'patient_id' => $this->patient_id,
            'patient' => $this->whenLoaded('patient', function () {
                return $this->patient->user ? [
                    'id' => $this->patient->id,
                    'nom' => $this->patient->user->nom,
                    'prenom' => $this->patient->user->prenom,
                ] : null;
            }),
            'etatGeneral' => $this->whenLoaded('etatGeneral', function () {
                return [
                    'id' => $this->etatGeneral->id,
                    'description' => $this->etatGeneral->description,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}