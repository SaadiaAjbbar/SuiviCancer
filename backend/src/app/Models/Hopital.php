<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;

class Hopital extends Model
{

    protected $fillable = ['nom', 'adresse', 'telephone', 'email'];

    public function admins()
    {
        return $this->hasOne(AdminHopital::class);
    }

    /**
     * Relation spécifique pour les Infirmières
     */
    public function infirmieres()
    {
        return $this->hasMany(Infermier::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
    public function medecins()
    {
        return $this->hasMany(Medecin::class);
    }


}
