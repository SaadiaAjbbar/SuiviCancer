<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtarGeneralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'consultation_id' => $this->consultation_id,
            'reponses_id' => $this->reponses_id,
            'consultation' => $this->whenLoaded('consultation', function () {
                return [
                    'id' => $this->consultation->id,
                    'date' => $this->consultation->date,
                    'gravite' => $this->consultation->gravite,
                ];
            }),
            'traitement' => $this->whenLoaded('traitement', function () {
                return $this->traitement ? [
                    'id' => $this->traitement->id,
                    'nom' => $this->traitement->nom,
                    'description' => $this->traitement->description,
                ] : null;
            }),
            'rendezVous' => $this->whenLoaded('rendezVous', function () {
                return $this->rendezVous ? [
                    'id' => $this->rendezVous->id,
                    'date' => $this->rendezVous->date,
                    'status' => $this->rendezVous->status,
                ] : null;
            }),
            'conseil' => $this->whenLoaded('conseil', function () {
                return $this->conseil ? [
                    'id' => $this->conseil->id,
                    'description' => $this->conseil->description,
                ] : null;
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
