<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymptomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'toxicite_id' => $this->toxicite_id,
            'toxicite' => $this->whenLoaded('toxicite', function () {
                return [
                    'id' => $this->toxicite->id,
                    'nom' => $this->toxicite->nom,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}