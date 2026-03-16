<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    //
     protected $fillable = [
        'utilisateur_id',
        'livre_isbn',
        'date_reser',
        'statut'=> 'active'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class,'livre_isbn','isbn');
    }
}
