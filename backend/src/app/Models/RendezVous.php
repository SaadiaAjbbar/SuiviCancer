<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    
    protected $fillable = ['date', 'status', 'etat_general_id'];
    public function etatGeneral()
    {
        return $this->belongsTo(EtatGeneral::class);
    }
}
