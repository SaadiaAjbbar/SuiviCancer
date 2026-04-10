<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['date', 'contenu', 'type', 'user_id'];

    // La notification appartient à un utilisateur (Patient, Medecin, etc.)
    public function patients()
    {
        return $this->belongsTo(Patient::class);
    }
    function etats_generales(){
        return $this->belongsTo(EtatGeneral::class);
    }
}
