<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = ['titre', 'patient_id'];

    public function etat_general()
    {
        return $this->hasOne(EtatGeneral::class,'reponse_id');
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function medecin()
    {
        return $this->belongsTo(Medecin::class, 'medecin_id');
    }
    public function reponse_questionnaires()
    {
        return $this->hasMany(reponse_questionnaires::class, 'reponses_id');
    }



}
