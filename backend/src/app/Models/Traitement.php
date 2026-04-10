<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traitement extends Model
{

    protected $fillable = ['nom', 'description', 'etat_general_id'];
    public function etatGeneral()
    {
        return $this->belongsTo(EtatGeneral::class);
    }
}
