<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtatGeneral extends Model
{
    
    protected $fillable = ['description', 'consultation_id', 'reponse_questionnaire_id'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
    public function reponseQuestionnaire()
    {
        return $this->belongsTo(ReponseQuestionnaire::class);
    }

    // Décisions optionnelles
    public function traitement()
    {
        return $this->hasOne(Traitement::class);
    }
    public function rendezVous()
    {
        return $this->hasOne(RendezVous::class);
    }
    public function conseil()
    {
        return $this->hasOne(Conseil::class);
    }
}
