<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    protected $fillable = ['user_id', 'specialite', 'hopital_id'];


    // Relation vers le compte utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un médecin suit plusieurs patients
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    // Un médecin effectue plusieurs consultations
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function hopitals()
    {
        return $this->belongsTo(Hopital::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function etat_general(){
        return $this->hasMany(EtatGeneral::class);
    }
}
