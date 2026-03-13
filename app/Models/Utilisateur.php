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
        'matricule',
        'historique_emprunts'
    ];
    public function emprunts()
{
    return $this->hasMany(Emprunt::class);
}
}
