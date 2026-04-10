<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    
    protected $fillable = ['titre'];
    public function medecin(){
        return $this->belongsTo(Medecin::class);
    }

    public function reponse(){
        return $this->hasMany(Reponse::class);
    }

}
