<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
   protected $table = 'rendez_vous';
    protected $fillable = ['date', 'status', 'etat_general_id', 'patient_id'];

    public function etatGeneral() {
        return $this->belongsTo(EtatGeneral::class);
    }

    public function patient() {
        return $this->belongsTo(Patient::class);
    }
}
