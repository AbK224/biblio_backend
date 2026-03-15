<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    //
    protected $fillable = [
        'nom',
        'prenom',
        'type',
        'matricule'
    ];
    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }
    
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
