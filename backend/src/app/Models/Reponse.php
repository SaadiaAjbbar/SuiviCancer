<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = ['titre', 'patient_id'];

    public function etats_generals()
    {
        return $this->hasOne(EtatGeneral::class);
    }
    public function patients()
    {
        return $this->belongsTo(Patient::class);
    }
    public function medecins()
    {
        return $this->belongsTo(Medecin::class);
    }
    public function reponse_questionnaires(){
        return $this->hasMany(reponse_questionnaires::class);
    }
    public function questions() {
    return $this->belongsToMany(Question::class, 'reponse_questionnaires', 'reponses_id', 'questions_id')
                ->withPivot('reponse');
}
}
