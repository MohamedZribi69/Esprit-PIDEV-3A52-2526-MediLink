<?php

namespace App\Service;

use App\Entity\Dons;

/**
 * Service d'analyse / scoring des dons pour aide à la décision admin.
 * Retourne un score et une décision suggérée (valider, rejeter, en_attente).
 */
class DonScoringService
{
    /**
     * Analyse un don et retourne un score + décision automatique.
     *
     * @return array{score: int, decision: string, decisionLabel: string}
     */
    public function analyser(Dons $don): array
    {
        $score = 50; // base

        $description = $don->getArticleDescription() ?? '';
        $details = $don->getDetailsSupplementaires() ?? '';
        $quantite = $don->getQuantite() ?? 0;
        $urgence = $don->getNiveauUrgence() ?? 'Moyen';

        if (mb_strlen($description) >= 20) {
            $score += 15;
        }
        if (mb_strlen($description) >= 50) {
            $score += 10;
        }
        if (mb_strlen(trim($details)) > 0) {
            $score += 5;
        }
        if ($quantite > 0) {
            $score += 5;
        }
        if ($quantite >= 5) {
            $score += 5;
        }

        switch (mb_strtolower($urgence)) {
            case 'haute':
            case 'élevée':
                $score += 10;
                break;
            case 'moyenne':
            case 'moyen':
                $score += 5;
                break;
        }

        $score = min(100, max(0, $score));

        if ($score >= 70) {
            $decision = 'valider';
            $decisionLabel = 'Validé (score élevé)';
        } elseif ($score <= 35) {
            $decision = 'rejeter';
            $decisionLabel = 'Rejeté (score insuffisant)';
        } else {
            $decision = 'en_attente';
            $decisionLabel = 'En attente de revue manuelle';
        }

        return [
            'score' => $score,
            'decision' => $decision,
            'decisionLabel' => $decisionLabel,
        ];
    }
}
