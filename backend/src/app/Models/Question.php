<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['titre' , 'medecin_id' ];


    public function medecin(){
        return $this->belongsTo(Medecin::class);
    }

    public function reponse_questionnaires(){
        return $this->hasMany(reponse_questionnaires::class);
    }

}
