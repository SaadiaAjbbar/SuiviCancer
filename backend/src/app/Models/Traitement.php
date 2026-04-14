<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traitement extends Model
{
    protected $table = 'traitements'; // T'akkedi men smiya f l-base de données

    protected $fillable = ['nom', 'description', 'etat_general_id', 'patient_id'];

    public function etatGeneral()
    {
        return $this->belongsTo(EtatGeneral::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
