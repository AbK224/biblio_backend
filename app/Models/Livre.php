<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    //
     protected $fillable = [
        'isbn',
        'titre',
        'auteur',
        'categorie',
        'annee_pub',
        'exemplaires_total',
        'exemplaires_disponible',
        'statut',
        'nbr_emprunts'
    ];
    // relation emprunt
    public function emprunts()
    {
        return $this->hasMany(Emprunt::class,'livre_isbn','isbn');
    }
}
