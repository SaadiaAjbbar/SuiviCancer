<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reponse_questionnaires extends Model
{
    protected $fillable = ['reponse', 'reponses_id', 'questions_id'];

    public function reponse()
    {
        return $this->belongsTo(Reponse::class,'reponses_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class,'questions_id');
    }
}
