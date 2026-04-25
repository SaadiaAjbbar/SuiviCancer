<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date_naissance' => $this->date_naissance,
            'sexe' => $this->sexe,
            'telephone' => $this->telephone,
            'hopital_id' => $this->hopital_id,
            'medecin_id' => $this->medecin_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'nom' => $this->user->nom,
                    'prenom' => $this->user->prenom,
                    'email' => $this->user->email,
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
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}