<?php

namespace App\Services;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Exception;

class EmpruntService
{

    private $limites = [
        'etudiant' => 3,
        'enseignant' => 3,
        'administratif' => 3
    ];

    /* public function creerEmprunt($data)
    {

        $utilisateur = Utilisateur::findOrFail($data['utilisateur_id']);
        $livre = Livre::where('isbn',$data['livre_isbn'])->firstOrFail();

        // vérifier disponibilité
        if($livre->exemplaires_disponible <= 0){
            throw new Exception("Livre indisponible");
        }

        // nombre d'emprunts en cours
        $empruntsEnCours = Emprunt::where('utilisateur_id',$utilisateur->id)
            ->where('statut','en cours')
            ->count();

        $limite = $this->limites[$utilisateur->type];

        if($empruntsEnCours >= $limite){
            throw new Exception("Limite d'emprunts atteinte");
        }

        $emprunt = Emprunt::create($data);

        // diminuer le stock
        $livre->exemplaires_disponible -= 1;
        $livre->save();

        return $emprunt;
        
    } */
    public function creerEmprunt($data)
    {

        $utilisateur = Utilisateur::findOrFail($data['utilisateur_id']);
        $livre = Livre::where('isbn',$data['livre_isbn'])->firstOrFail();

        if($livre->exemplaires_disponible <= 0){
            throw new \Exception("Livre indisponible");
        }
        // Empêcher double emprunt du même livre
        $existe = Emprunt::where('utilisateur_id',$utilisateur->id)
            ->where('livre_isbn',$livre->isbn)
            ->where('statut','en cours')
            ->exists();

        if($existe){
            throw new \Exception("Vous avez déjà emprunté ce livre");
        }

        // nombre d'emprunts en cours
        $empruntsEnCours = Emprunt::where('utilisateur_id',$utilisateur->id)
            ->where('statut','en cours')
            ->count();

        $limite = $this->limites[$utilisateur->type];

        if($empruntsEnCours >= $limite){
            throw new Exception("Limite d'emprunts atteinte");
        }
        // calcul du nbre de jrs en fonction du type de l'utilsateur
        $jours = $this->dureeEmprunt($utilisateur->type);

        $dateEmprunt = Carbon::parse($data['date_emprunt']);

        $dateRetour = $dateEmprunt->copy()->addDays($jours);
        // crée l'emprunt après toutes les verifications
        $emprunt = Emprunt::create([
            'utilisateur_id' => $utilisateur->id,
            'livre_isbn' => $livre->isbn,
            'date_emprunt' => $dateEmprunt,
            'date_retour_prevue' => $dateRetour,
            'statut' => 'en cours'
        ]);
        // diminuer les exemplaires
        $livre->decrement('exemplaires_disponible');
        if($livre->exemplaires_disponible == 0){
            $livre->statut = 'indisponible';
            $livre->save();
        }

        // augmenter nombre emprunts
        $livre->increment('nbr_emprunts');
        return $emprunt;
    }




    public function retournerLivre($id)
    {

        $emprunt = Emprunt::findOrFail($id);

        if($emprunt->statut === 'retourné'){
            throw new Exception("Livre déjà retourné");
        }

        $livre = Livre::where('isbn',$emprunt->livre_isbn)->first();

        $emprunt->statut = 'retourné';
        $emprunt->save();

        $livre->increment('exemplaires_disponible');
        if($livre->exemplaires_disponible > 0){
            $livre->statut = 'disponible';
            $livre->save();
        }
        $livre->decrement('nbr_emprunts');
        $livre->save();

        return $emprunt;

    }
    private function dureeEmprunt($type)
    {

        $durees = [

            'etudiant' => 10,
            'enseignant' => 15,
            'administratif' => 12

        ];

        return $durees[$type] ?? 10;
    }

    

}

