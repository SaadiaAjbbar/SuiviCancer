<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'nom',
    'prenom',
    'email',
    'mot_de_passe',
    'role'
])]
#[Hidden(['mot_de_passe', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mot_de_passe' => 'hashed',
        ];
    }
    public function adminHopital()
    {
        // Relation 1:1 avec la table de liaison
        return $this->hasOne(AdminHopital::class, 'user_id');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function infermier()
    {
        return $this->hasOne(Infermier::class);
    }

    public function medecin()
    {
        // HasOne hit kol user 3ndo profil medecin wa7ed
        return $this->hasOne(Medecin::class, 'user_id');
    }
}
