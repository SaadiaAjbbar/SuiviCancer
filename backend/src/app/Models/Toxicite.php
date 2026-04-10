<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toxicite extends Model
{
    
    protected $fillable = ['nom', 'description', 'hopital_id'];

    public function symptomes(){
        return $this->hasMany(Symptome::class);
    }

    public function admin(){
        return $this->belongsTo(User::class)->where('role', 'ADMINHOPITAL');
    }
    public function consultations() {
        return $this->belongsToMany(Consultation::class, 'toxicite_consultation');
    }
}
