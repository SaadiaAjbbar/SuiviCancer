<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = ['titre'];


    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
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

}
