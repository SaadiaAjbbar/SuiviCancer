<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    
    protected $fillable = ['date', 'gravite', 'patient_id', 'medecin_id'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
    public function etatGeneral()
    {
        return $this->hasOne(EtatGeneral::class);
    }

    // Relations Many-to-Many
    public function toxicites()
    {
        return $this->belongsToMany(Toxicite::class, 'toxicite_consultation');
    }

    public function symptomes()
    {
        return $this->belongsToMany(Symptome::class, 'symptome_consultation');
    }
}
