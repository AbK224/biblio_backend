<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    protected $casts = [
    'date_emprunt' => 'datetime',
    'date_retour_prevue' => 'datetime',
    'date_retour_effective' => 'datetime'
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
    public function getPenaliteAttribute()
    {

        if(!$this->date_retour_effective){
            return 0;
        }

        $retard = Carbon::parse($this->date_retour_effective)
            ->diffInDays($this->date_retour_prevue,false);

        if($retard <= 0){
            return 0;
        }

        return $retard * 500;
    }
}
