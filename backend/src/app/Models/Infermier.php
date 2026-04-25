<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Infermier extends Model
{
    protected $fillable = ['user_id', 'hopital_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function hopital() {
        return $this->belongsTo(Hopital::class);
    }
}
