<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient' => $this->whenLoaded('patients', function () {
                return $this->patients?->user ? [
                    'id' => $this->patients->id,
                    'nom' => $this->patients->user->nom,
                    'prenom' => $this->patients->user->prenom,
                ] : null;
            }),
            'reponse_questionnaires' => $this->whenLoaded('reponse_questionnaires', function () {
                return $this->reponse_questionnaires->map(fn($r) => [
                    'id' => $r->id,
                    'question_id' => $r->questions_id,
                    'question' => $r->question ? [
                        'id' => $r->question->id,
                        'titre' => $r->question->titre,
                    ] : null,
                    'reponse' => $r->reponse,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}