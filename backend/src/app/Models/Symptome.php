<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Symptome extends Model
{
    protected $fillable = ['nom', 'description', 'toxicite_id'];

    public function toxicite()
    {
        return $this->belongsTo(Toxicite::class);
    }

    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'symptome_consultation');
    }
}
