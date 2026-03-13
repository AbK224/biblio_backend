<?php

namespace App\Services;

use App\Models\Livre;
use App\Models\Emprunt;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmpruntService
{

    private $dureeEmprunt = [
        "etudiant" => 10,
        "enseignant" => 15,
        "personnel administratif" => 12
    ];

    public function emprunterLivre($data)
    {

        return DB::transaction(function () use ($data) {

            $livre = Livre::where('isbn',$data['livre_isbn'])->firstOrFail();

            if($livre->exemplaires_disponibles <= 0){
                throw new \Exception("Livre indisponible");
            }

            $utilisateur = Utilisateur::findOrFail($data['utilisateur_id']);

            $jours = $this->dureeEmprunt[$utilisateur->type];

            $dateEmprunt = Carbon::now();
            $dateRetourPrevue = $dateEmprunt->copy()->addDays($jours);

            $emprunt = Emprunt::create([
                'utilisateur_id' => $data['utilisateur_id'],
                'livre_isbn' => $data['livre_isbn'],
                'date_emprunt' => $dateEmprunt,
                'date_retour_prevue' => $dateRetourPrevue,
                'statut' => 'en cours'
            ]);

            $livre->exemplaires_disponibles -= 1;
            $livre->nbr_emprunts += 1;

            if($livre->exemplaires_disponibles == 0){
                $livre->statut = "indisponible";
            }

            $livre->save();

            return $emprunt;

        });
    }

}