<?php

namespace App\Services;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Exception;

class EmpruntService
{
    /**
     * Limite maximale d'emprunts en cours par type d'utilisateur
     */
    private $limites = [
        'etudiant' => 3,
        'enseignant' => 3,
        'administratif' => 3,
    ];

    /**
     * Créer un nouvel emprunt
     *
     * Règles métier :
     * - le livre doit être disponible
     * - l'utilisateur ne doit pas avoir déjà emprunté ce même livre en cours
     * - l'utilisateur ne doit pas dépasser sa limite d'emprunts
     * - la date_retour_prevue est calculée automatiquement selon le type d'utilisateur
     */
    public function creerEmprunt(array $data)
    {
        // Récupération de l'utilisateur et du livre
        $utilisateur = Utilisateur::findOrFail($data['utilisateur_id']);
        $livre = Livre::where('isbn', $data['livre_isbn'])->firstOrFail();

        // Vérifier si le livre est disponible
        $this->verifierDisponibiliteLivre($livre);

        // Vérifier que l'utilisateur n'a pas déjà ce livre en cours
        $this->verifierDoubleEmprunt($utilisateur->id, $livre->isbn);

        // Vérifier la limite d'emprunts de l'utilisateur
        $this->verifierLimiteEmprunts($utilisateur);

        // Préparation des dates
        $dateEmprunt = Carbon::parse($data['date_emprunt']);
        $dateRetourPrevue = $this->calculerDateRetourPrevue($utilisateur->type, $dateEmprunt);

        // Création de l'emprunt
        $emprunt = Emprunt::create([
            'utilisateur_id' => $utilisateur->id,
            'livre_isbn' => $livre->isbn,
            'date_emprunt' => $dateEmprunt,
            'date_retour_prevue' => $dateRetourPrevue,
            'statut' => 'en cours',
        ]);

        // Mise à jour du stock du livre
        $this->decrementerStockLivre($livre);

        // Incrémenter le nombre total d'emprunts du livre
        $livre->increment('nbr_emprunts');

        return $emprunt->fresh(['utilisateur', 'livre']);
    }

    /**
     * Modifier un emprunt existant
     *
     * Cas gérés :
     * - changement d'utilisateur => recalcul automatique de date_retour_prevue
     * - changement de date_emprunt => recalcul automatique de date_retour_prevue
     * - changement de livre => ajustement du stock ancien/nouveau livre
     * - statut = "retourné" => passage par la logique métier de retour
     */
    public function modifierEmprunt(int $id, array $data)
    {
        $emprunt = Emprunt::findOrFail($id);

        // Si l'utilisateur veut marquer l'emprunt comme retourné,
        // on utilise la logique métier prévue pour le retour.
        if (isset($data['statut']) && $data['statut'] === 'retourné') {
            return $this->retournerLivre($id);
        }

        // On récupère les valeurs finales après modification :
        // soit les nouvelles valeurs envoyées, soit les anciennes
        $nouvelUtilisateurId = $data['utilisateur_id'] ?? $emprunt->utilisateur_id;
        $nouveauLivreIsbn = $data['livre_isbn'] ?? $emprunt->livre_isbn;

        $utilisateur = Utilisateur::findOrFail($nouvelUtilisateurId);
        $nouveauLivre = Livre::where('isbn', $nouveauLivreIsbn)->firstOrFail();

        // On détermine la date d'emprunt finale
        $dateEmprunt = isset($data['date_emprunt'])
            ? Carbon::parse($data['date_emprunt'])
            : Carbon::parse($emprunt->date_emprunt);

        /**
         * Si l'utilisateur change OU si la date_emprunt change,
         * il faut recalculer automatiquement la date_retour_prevue
         * selon le type de ce nouvel utilisateur.
         */
        if (isset($data['utilisateur_id']) || isset($data['date_emprunt'])) {
            $data['date_retour_prevue'] = $this->calculerDateRetourPrevue(
                $utilisateur->type,
                $dateEmprunt
            );
        }

        /**
         * Si le livre change :
         * - vérifier que le nouveau livre est disponible
         * - remettre l'ancien livre en stock
         * - retirer un exemplaire du nouveau livre
         */
        if (isset($data['livre_isbn']) && $data['livre_isbn'] !== $emprunt->livre_isbn) {
            $ancienLivre = Livre::where('isbn', $emprunt->livre_isbn)->firstOrFail();

            $this->verifierDisponibiliteLivre($nouveauLivre);

            // Vérifier aussi que le nouvel utilisateur n'a pas déjà ce nouveau livre en cours
            $this->verifierDoubleEmprunt($nouvelUtilisateurId, $nouveauLivre->isbn, $emprunt->id);

            // On remet l'ancien livre en stock
            $this->incrementerStockLivre($ancienLivre);

            // On retire un exemplaire du nouveau livre
            $this->decrementerStockLivre($nouveauLivre);
        }

        /**
         * Si l'utilisateur change, on peut revérifier :
         * - qu'il ne dépasse pas sa limite
         * - qu'il n'a pas déjà ce livre en cours
         *
         * On exclut l'emprunt courant grâce au 3e paramètre.
         */
        if (isset($data['utilisateur_id'])) {
            $this->verifierLimiteEmprunts($utilisateur, $emprunt->id);
            $this->verifierDoubleEmprunt($utilisateur->id, $nouveauLivre->isbn, $emprunt->id);
        }

        // Mise à jour finale de l'emprunt
        $emprunt->update($data);

        return $emprunt->fresh(['utilisateur', 'livre']);
    }

    /**
     * Retourner un livre
     *
     * Règles métier :
     * - on ne peut pas retourner un livre déjà retourné
     * - on met le statut à "retourné"
     * - on renseigne la date_retour_effective
     * - on remet le livre en stock
     */
    public function retournerLivre(int $id)
    {
        $emprunt = Emprunt::findOrFail($id);

        if ($emprunt->statut === 'retourné') {
            throw new Exception("Livre déjà retourné");
        }

        $livre = Livre::where('isbn', $emprunt->livre_isbn)->firstOrFail();

        // Mise à jour de l'emprunt
        $emprunt->update([
            'statut' => 'retourné',
            'date_retour_effective' => now(),
        ]);

        // Remise en stock du livre
        $this->incrementerStockLivre($livre);

        return $emprunt->fresh(['utilisateur', 'livre']);
    }

    /**
     * Vérifie que le livre est bien disponible
     */
    private function verifierDisponibiliteLivre(Livre $livre): void
    {
        if ($livre->exemplaires_disponible <= 0) {
            throw new Exception("Livre indisponible");
        }
    }

    /**
     * Vérifie qu'un utilisateur n'a pas déjà emprunté le même livre
     *
     * Le paramètre $empruntIdAExclure sert lors de la modification,
     * pour ne pas considérer l'emprunt courant comme un doublon.
     */
    private function verifierDoubleEmprunt(int $utilisateurId, string $livreIsbn, ?int $empruntIdAExclure = null): void
    {
        $query = Emprunt::where('utilisateur_id', $utilisateurId)
            ->where('livre_isbn', $livreIsbn)
            ->where('statut', 'en cours');

        if ($empruntIdAExclure) {
            $query->where('id', '!=', $empruntIdAExclure);
        }

        if ($query->exists()) {
            throw new Exception("Cet utilisateur a déjà emprunté ce livre");
        }
    }

    /**
     * Vérifie que l'utilisateur ne dépasse pas sa limite d'emprunts en cours
     *
     * Le paramètre $empruntIdAExclure sert lors d'une modification.
     */
    private function verifierLimiteEmprunts(Utilisateur $utilisateur, ?int $empruntIdAExclure = null): void
    {
        $query = Emprunt::where('utilisateur_id', $utilisateur->id)
            ->where('statut', 'en cours');

        if ($empruntIdAExclure) {
            $query->where('id', '!=', $empruntIdAExclure);
        }

        $empruntsEnCours = $query->count();
        $limite = $this->limites[$utilisateur->type] ?? 3;

        if ($empruntsEnCours >= $limite) {
            throw new Exception("Limite d'emprunts atteinte pour cet utilisateur");
        }
    }

    /**
     * Calcule la date de retour prévue selon le type d'utilisateur
     */
    private function calculerDateRetourPrevue(string $typeUtilisateur, Carbon $dateEmprunt): Carbon
    {
        $jours = $this->dureeEmprunt($typeUtilisateur);

        return $dateEmprunt->copy()->addDays($jours);
    }

    /**
     * Retourne le nombre de jours d'emprunt autorisés selon le type
     */
    private function dureeEmprunt(string $type): int
    {
        $durees = [
            'etudiant' => 10,
            'enseignant' => 15,
            'administratif' => 12,
        ];

        return $durees[$type] ?? 10;
    }

    /**
     * Diminue le stock disponible d'un livre
     * et met à jour son statut si nécessaire
     */
    private function decrementerStockLivre(Livre $livre): void
    {
        $livre->decrement('exemplaires_disponible');

        if ($livre->fresh()->exemplaires_disponible <= 0) {
            $livre->statut = 'indisponible';
            $livre->save();
        }
    }

    /**
     * Augmente le stock disponible d'un livre
     * et met à jour son statut si nécessaire
     */
    private function incrementerStockLivre(Livre $livre): void
    {
        $livre->increment('exemplaires_disponible');

        if ($livre->fresh()->exemplaires_disponible > 0) {
            $livre->statut = 'disponible';
            $livre->save();
        }
    }
}