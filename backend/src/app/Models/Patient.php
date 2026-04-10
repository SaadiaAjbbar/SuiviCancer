<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['user_id', 'date_naissance', 'sexe', 'telephone', 'hopital_id', 'medecin_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hopital()
    {
        return $this->belongsTo(Hopital::class);
    }
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
