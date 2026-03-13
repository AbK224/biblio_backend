<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emprunt extends Model
{
    //
    protected $fillable = [
        'utilisateur_id',
        'livre_isbn',
        'date_emprunt',
        'date_retour_prevue',
        'date_retour_effective',
        'renouvellements',
        'statut'
    ];

    // relation utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    // relation livre
    public function livre()
    {
        return $this->belongsTo(Livre::class, 'livre_isbn', 'isbn');
    }
}
