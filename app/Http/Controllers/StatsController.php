<?php

namespace App\Http\Controllers;

use App\Models\Livre;
use App\Models\Utilisateur;
use App\Models\Emprunt;

class StatsController extends Controller
{
    /**
     * Retourne les statistiques du dashboard
     */
    public function index()
    {
        // 1. Total des livres
        $totalLivres = Livre::count();

        // 2. Total des membres
        $totalMembres = Utilisateur::count();

        // 3. Emprunts actifs = emprunts encore en cours
        $empruntsActifs = Emprunt::where('statut', 'en cours')->count();

        // 4. Retards = emprunts en cours dont la date prévue est dépassée
        $retards = Emprunt::where('statut', 'en cours')
            ->where('date_retour_prevue', '<', now())
            ->count();

        // 5. Activité récente
        $activiteRecente = Emprunt::with(['utilisateur', 'livre'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($emprunt) {
                return [
                    'id' => $emprunt->id,
                    'type' => 'emprunt',
                    'message' => ($emprunt->utilisateur->nom ?? '') . ' ' .
                                 ($emprunt->utilisateur->prenom ?? '') .
                                 ' a emprunté "' .
                                 ($emprunt->livre->titre ?? 'Livre inconnu') . '"',
                    'date' => $emprunt->created_at,
                    'statut' => $emprunt->statut,
                ];
            });

        // 6. Livres populaires
        $livresPopulaires = Livre::orderByDesc('nbr_emprunts')
            ->take(5)
            ->get([
                'isbn',
                'titre',
                'auteur',
                'categorie',
                'nbr_emprunts'
            ]);

        return response()->json([
            'total_livres' => $totalLivres,
            'total_membres' => $totalMembres,
            'emprunts_actifs' => $empruntsActifs,
            'retards' => $retards,
            'activite_recente' => $activiteRecente,
            'livres_populaires' => $livresPopulaires,
        ]);
    }
}