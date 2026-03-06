<?php

namespace App\Service;

use App\Entity\Dons;

/**
 * Service métier pour la gestion des dons.
 * Règles métier à valider par les tests unitaires :
 * 1. La quantité saisie doit être strictement positive.
 * 2. La date d'expiration doit être postérieure à aujourd'hui (ou vide).
 */
final class DonManager
{
    /**
     * Valide un don selon les règles métier.
     *
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Dons $don): bool
    {
        $quantite = $don->getQuantite();
        if ($quantite === null || $quantite < 1) {
            throw new \InvalidArgumentException('La quantité doit être strictement positive.');
        }

        $dateExp = $don->getDateExpiration();
        if ($dateExp !== null) {
            $today = new \DateTimeImmutable('today');
            if ($dateExp <= $today) {
                throw new \InvalidArgumentException('La date d\'expiration doit être postérieure à aujourd\'hui.');
            }
        }

        return true;
    }
}
