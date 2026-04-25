<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conseil extends Model
{
    protected $table = 'conseils';

    protected $fillable = ['date', 'description', 'etat_general_id', 'patient_id'];

    public function etatGeneral()
    {
        return $this->belongsTo(EtatGeneral::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
