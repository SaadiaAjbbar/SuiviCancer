<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToxiciteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'hopital_id' => $this->hopital_id,
            'symptomes' => $this->whenLoaded('symptomes', function () {
                return $this->symptomes->map(fn($s) => [
                    'id' => $s->id,
                    'nom' => $s->nom,
                    'description' => $s->description,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}