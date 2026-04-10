<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseQuestionnaire extends Model
{
    protected $fillable = ['reponse', 'patient_id'];
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function etatGeneral()
    {
        return $this->hasOne(EtatGeneral::class);
    }
}
